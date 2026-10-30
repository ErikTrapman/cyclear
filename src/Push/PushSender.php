<?php declare(strict_types=1);

namespace App\Push;

use App\Entity\PushSubscription;
use Doctrine\ORM\EntityManagerInterface;
use Minishlink\WebPush\MessageSentReport;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Sends push notifications to the browsers' push services, signed with the VAPID keys.
 */
class PushSender
{
    /** Push services drop a notification that couldn't be delivered within a day; old news isn't worth showing. */
    private const int TTL = 86400;

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ClientInterface $httpClient,
        private readonly RequestFactoryInterface $requestFactory,
        private readonly StreamFactoryInterface $streamFactory,
        private readonly LoggerInterface $logger,
        #[Autowire('%env(VAPID_PUBLIC_KEY)%')]
        private readonly string $publicKey,
        #[Autowire('%env(VAPID_PRIVATE_KEY)%')]
        private readonly string $privateKey,
        #[Autowire('mailto:%corresponding_address%')]
        private readonly string $subject,
    ) {
    }

    public function isConfigured(): bool
    {
        return '' !== $this->publicKey && '' !== $this->privateKey;
    }

    /**
     * Subscriptions the push service no longer knows (the user revoked permission or cleared the browser) are removed.
     *
     * @param iterable<PushSubscription> $subscriptions
     *
     * @return MessageSentReport[]
     */
    public function send(PushMessage $message, iterable $subscriptions): array
    {
        if (!$this->isConfigured()) {
            $this->logger->warning('Push notification not sent: VAPID_PUBLIC_KEY and VAPID_PRIVATE_KEY are not set.');
            return [];
        }

        $webPush = new WebPush(
            ['VAPID' => ['subject' => $this->subject, 'publicKey' => $this->publicKey, 'privateKey' => $this->privateKey]],
            ['TTL' => self::TTL],
            $this->httpClient,
            $this->requestFactory,
            $this->streamFactory,
            logger: $this->logger,
        );
        $payload = json_encode(['title' => $message->title, 'body' => $message->body, 'url' => $message->url], JSON_THROW_ON_ERROR);
        $byEndpoint = [];
        foreach ($subscriptions as $subscription) {
            $byEndpoint[$subscription->getEndpoint()] = $subscription;
            $webPush->queueNotification(
                new Subscription($subscription->getEndpoint(), $subscription->getP256dh(), $subscription->getAuth(), 'aes128gcm'),
                $payload,
            );
        }

        $reports = [];
        foreach ($webPush->flush() as $report) {
            $reports[] = $report;
            if ($report->isSuccess()) {
                continue;
            }
            $subscription = $byEndpoint[$report->getEndpoint()] ?? null;
            if ($report->isSubscriptionExpired() && null !== $subscription) {
                $this->em->remove($subscription);
            } else {
                $this->logger->warning('Push notification to {endpoint} failed: {reason}', [
                    'endpoint' => $report->getEndpoint(),
                    'reason' => $report->getReason(),
                ]);
            }
        }
        $this->em->flush();

        return $reports;
    }
}
