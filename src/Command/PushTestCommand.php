<?php declare(strict_types=1);

namespace App\Command;

use App\Push\PushMessage;
use App\Push\PushSender;
use App\Repository\PushSubscriptionRepository;
use App\Repository\UserRepository;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Sends right away, without the message queue, and shows what each push service answered.
 */
#[AsCommand(name: 'cyclear:push:test', description: 'Sends a test push notification to the browsers of a user, or of everyone')]
class PushTestCommand
{
    public function __construct(
        private readonly PushSender $pushSender,
        private readonly PushSubscriptionRepository $subscriptionRepository,
        private readonly UserRepository $userRepository,
    ) {
    }

    public function __invoke(SymfonyStyle $io, #[Argument('Leave out to send to everyone')] ?string $username = null): int
    {
        if (!$this->pushSender->isConfigured()) {
            $io->error('Set VAPID_PUBLIC_KEY and VAPID_PRIVATE_KEY first; cyclear:push:vapid-keys generates them.');
            return Command::FAILURE;
        }
        if (null === $username) {
            $subscriptions = $this->subscriptionRepository->findAll();
        } else {
            $user = $this->userRepository->loadUserByIdentifier($username);
            if (null === $user) {
                $io->error(sprintf('There is no user "%s".', $username));
                return Command::FAILURE;
            }
            $subscriptions = $this->subscriptionRepository->findBy(['user' => $user]);
        }
        if ([] === $subscriptions) {
            $io->warning('No browser has notifications on; turn them on with "Meldingen" in the menu.');
            return Command::SUCCESS;
        }

        $failed = 0;
        foreach ($this->pushSender->send(new PushMessage('Cyclear', 'Dit is een testmelding.', '/'), $subscriptions) as $report) {
            $host = parse_url($report->getEndpoint(), PHP_URL_HOST);
            if ($report->isSuccess()) {
                $io->writeln(sprintf('<info>sent</info> %s', $host));
            } else {
                ++$failed;
                $io->writeln(sprintf('<error>failed</error> %s: %s%s', $host, $report->getReason(), $report->isSubscriptionExpired() ? ' (removed)' : ''));
            }
        }

        return 0 === $failed ? Command::SUCCESS : Command::FAILURE;
    }
}
