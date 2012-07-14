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
    private $em;

    /**
     * 
     * @param EntityManager $em
     * @param Transfer $entity
     */
    public function __construct(EntityManager $em) {
        $this->em = $em;
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
        $transfer = new Transfer();
        $transfer->setRenner($renner);
        $transfer->setPloegVan($renner->getPloeg());
        $transfer->setPloegNaar($ploegNaar);
        $transfer->setDatum($datum);
        $this->em->persist($transfer);

        $renner->setPloeg($ploegNaar);
        $this->em->persist($renner);
        $transfer->setAdminTransfer(true);
        return $transfer;
    }

    public function doUserTransfer(Ploeg $ploeg, Renner $rennerUit, Renner $rennerIn) {
        // behandel de uitgaande transfer eerst
        $transfer = new Transfer();
        $transfer->setRenner($rennerUit);
        $transfer->setPloegVan($ploeg);
        $transfer->setPloegNaar(null);
        $transfer->setDatum(new \DateTime());
        $this->em->persist($transfer);
        $rennerUit->setPloeg(null);
        // de binnenkomende transfer
        $transfer = new Transfer();
        $transfer->setRenner($rennerIn);
        $transfer->setPloegVan(null);
        $transfer->setPloegNaar($ploeg);
        $transfer->setDatum(new \DateTime());
        $this->em->persist($transfer);
        $rennerIn->setPloeg($ploeg);
        return true;
    }

}