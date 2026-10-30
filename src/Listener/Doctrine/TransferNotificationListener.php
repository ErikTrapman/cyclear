<?php declare(strict_types=1);

namespace App\Listener\Doctrine;

use App\Entity\Transfer;
use App\Message\TransferNotification;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Queues a push notification for each transfer a manager makes, once it is stored. Draft and admin transfers are
 * left out.
 */
#[AsDoctrineListener(event: Events::postPersist)]
#[AsDoctrineListener(event: Events::postFlush)]
class TransferNotificationListener
{
    /** @var Transfer[] */
    private array $transfers = [];

    public function __construct(
        private readonly MessageBusInterface $bus,
    ) {
    }

    public function postPersist(PostPersistEventArgs $args): void
    {
        $transfer = $args->getObject();
        if ($transfer instanceof Transfer && Transfer::USERTRANSFER === $transfer->getTransferType() && null !== $transfer->getPloegNaar()) {
            $this->transfers[] = $transfer;
        }
    }

    public function postFlush(PostFlushEventArgs $args): void
    {
        $transfers = $this->transfers;
        $this->transfers = [];
        foreach ($transfers as $transfer) {
            $this->bus->dispatch(new TransferNotification($transfer->getId()));
        }
    }
}
