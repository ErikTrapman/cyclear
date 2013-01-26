<?php

namespace Cyclear\GameBundle\Listener\Doctrine;

use Cyclear\GameBundle\Entity\Transfer;
use Doctrine\ORM\Event\LifecycleEventArgs;

class TransferListener
{
    private $tweeter;

    public function __construct($tweeter)
    {
        $this->tweeter = $tweeter;
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $entityManager = $args->getEntityManager();

        if ($entity instanceof Transfer) {
            if (null !== $entity->getPloegNaar() && $entity->getTransferType() != Transfer::DRAFTTRANSFER) {
                $inversion = $entityManager->getRepository("CyclearGameBundle:Transfer")->getInversion($entity);
                $ploegNaar = $entity->getPloegNaar()->getAfkorting();
                $rennerUit = $inversion->getRenner()->getNaam();
                $rennerIn = $entity->getRenner()->getNaam();
                $msg = sprintf('Transfer voor %s ~ [IN] %s, [UIT] %s', $ploegNaar, $rennerIn, $rennerUit);
                try { 
                    $this->tweeter->sendTweet($msg);
                } catch(\Exception $e){
                    // do nothing. Exception is logged
                }
            }
        }
    }
}