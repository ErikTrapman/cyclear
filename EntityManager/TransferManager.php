<?php

namespace Cyclear\GameBundle\EntityManager;

use Doctrine\ORM\EntityManager;
use Cyclear\GameBundle\Entity\Transfer,
    Cyclear\GameBundle\Entity\Renner,
    Cyclear\GameBundle\Entity\Ploeg;

class TransferManager {

    /**
     * 
     * @var EntityManager
     */
    private $doctrine;

    /**
     * 
     * @var Transfer
     */
    private $entity;

    /**
     * 
     * @param EntityManager $registry
     * @param Transfer $entity
     */
    public function __construct(EntityManager $registry) {
        $this->doctrine = $registry;
    }

    /**
     * 
     * @param Renner $renner
     * @param Ploeg $ploegVan
     * @param Ploeg $ploegNaar
     * @param \DateTime $datum
     * @return Transfer
     */
    public function doAdminTransfer(Renner $renner, Ploeg $ploegNaar = null, \DateTime $datum = null) {
        $this->entity = new Transfer();
        $this->entity->setRenner($renner);
        $this->entity->setPloegVan($renner->getPloeg());
        $this->entity->setPloegNaar($ploegNaar);
        $this->entity->setDatum($datum);
        $this->doctrine->persist($this->entity);

        $renner->setPloeg($ploegNaar);
        $this->doctrine->persist($renner);
        
        return $this->entity;
    }

}