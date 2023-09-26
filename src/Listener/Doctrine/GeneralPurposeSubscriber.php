<?php declare(strict_types=1);

namespace App\Listener\Doctrine;

use App\Entity\User;
use App\Repository\TransferRepository;
use App\Repository\UitslagRepository;
use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class GeneralPurposeSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly TagAwareCacheInterface $cache,
    ) {
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::onFlush,
        ];
    }

    public function onFlush(OnFlushEventArgs $args): void
    {
        // Finetune this. Try not to invalidate cache in case we have a login of a User. All other cases are currently valid.
        $user = $args->getObjectManager()->getUnitOfWork()->getScheduledEntityUpdates()[0] ?? false;
        if ($user instanceof User) {
            return;
        }
        $this->cache->invalidateTags([UitslagRepository::CACHE_TAG, TransferRepository::CACHE_TAG]);
    }
}
