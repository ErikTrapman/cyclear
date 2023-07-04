<?php declare(strict_types=1);

namespace App\Listener\Doctrine;

use App\Entity\Transfer;
use App\Entity\User;
use App\Repository\TransferRepository;
use App\Repository\UitslagRepository;
use App\Twitter\Tweeter;
use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class GeneralPurposeSubscriber implements EventSubscriberInterface
{
    private $tweetMsgs = [
        '%team% gives %out% the boot and welcomes %in% into the team',
        '%team% says "Hi" to %in% and "Bye" to %out%',
        '%team% fires %out% and hires %in%',
        '%team% kicks %out% out in favour of %in%',
        '.%out% got the sack, %in% a contract at %team%',
        'High expectations at %team% for new signing %in%; %out% failed to extend',
    ];

    public function __construct(
        private readonly Tweeter $tweeter,
        private readonly TranslatorInterface $translator,
        private readonly TagAwareCacheInterface $cache,
    ) {
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::postPersist,
            Events::onFlush,
        ];
    }

    public function postPersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();
        if ($entity instanceof Transfer) {
            if (null !== $entity->getPloegNaar() && Transfer::DRAFTTRANSFER != $entity->getTransferType()) {
                $inversion = $entity->getInversionTransfer();
                $ploegNaar = $entity->getPloegNaar()->getAfkorting();
                $rennerUit = null;
                if (null !== $inversion) {
                    $rennerUit = $inversion->getRenner();
                }
                $rennerIn = $entity->getRenner();
                if ($rennerIn->getTwitter() && $rennerUit->getTwitter()) {
                    $rennerInDisplay = $rennerIn->getTwitter() ? '@' . $rennerIn->getTwitter() : $rennerIn->getNaam();
                    $rennerUitDisplay = '';
                    if ($rennerUit) {
                        $rennerUitDisplay = $rennerUit->getTwitter() ? '@' . $rennerUit->getTwitter() : $rennerUit->getNaam();
                    }
                    $params = ['%team%' => $ploegNaar, '%in%' => $rennerInDisplay, '%out%' => $rennerUitDisplay];
                    $msg = $this->translator->trans($this->getRandomTweet(), $params);
                    try {
                        $this->tweeter->sendTweet($msg);
                    } catch (\Exception $e) {
                        // do nothing. Exception is logged
                    }
                }
            }
        }
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

    private function getRandomTweet(): string
    {
        $index = rand(0, count($this->tweetMsgs) - 1);
        return $this->tweetMsgs[$index];
    }
}
