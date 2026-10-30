<?php declare(strict_types=1);

namespace App\MessageHandler;

use App\Entity\Transfer;
use App\Message\TransferNotification;
use App\Push\PushMessage;
use App\Push\PushSender;
use App\Repository\PushSubscriptionRepository;
use App\Repository\TransferRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[AsMessageHandler]
class TransferNotificationHandler
{
    public function __construct(
        private readonly TransferRepository $transferRepository,
        private readonly PushSubscriptionRepository $subscriptionRepository,
        private readonly PushSender $pushSender,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function __invoke(TransferNotification $notification): void
    {
        $transfer = $this->transferRepository->find($notification->transferId);
        // Gone when an admin reverted it before the notification went out
        if (!$transfer instanceof Transfer || null === $transfer->getPloegNaar()) {
            return;
        }
        $ploeg = $transfer->getPloegNaar();
        $body = null !== $transfer->getInversionTransfer()
            ? $transfer->getInversionTransfer()->getRenner()->getNaam() . ' vertrekt.'
            : '';
        if ($transfer->getUserComment()) {
            $body = trim($body . ' "' . $transfer->getUserComment() . '"');
        }

        $this->pushSender->send(
            new PushMessage(
                $ploeg->getNaam() . ' haalt ' . $transfer->getRenner()->getNaam() . ' binnen',
                $body,
                $this->urlGenerator->generate('transfer_list', ['seizoen' => $transfer->getSeizoen()->getSlug()]),
            ),
            // Managers know about their own transfers
            $this->subscriptionRepository->findAllExceptUser($ploeg->getUser()),
        );
    }
}
