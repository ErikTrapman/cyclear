<?php

namespace Cyclear\GameBundle\Listener;

class TransferListener
{
    private $contractManager;

    public function __construct($contractManager)
    {
        $this->contractManager = $contractManager;
    }

    public function postPersist(\Doctrine\ORM\Event\LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $entityManager = $args->getEntityManager();

        if ($entity instanceof \Cyclear\GameBundle\Entity\Transfer) {

            if (null === $entity->getPloegNaar()) {
                $this->contractManager->releaseRenner($entity->getRenner());
            } else {
                $this->contractManager->createContract($entity->getRenner(), $entity->getPloegNaar());
            }
            echo 'a';
            die;
        }
        echo 'b';
        die;
    }
    
    private function releaseRenner(){
        
    }
}