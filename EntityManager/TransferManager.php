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
    public function doAdminTransfer(Renner $renner, Ploeg $ploegNaar, \DateTime $datum, $transferType, $seizoen) {
        $identifier = uniqid();
        $transfer = new Transfer();
        $transfer->setRenner($renner);
        $transfer->setPloegVan($renner->getPloeg());
        $transfer->setPloegNaar($ploegNaar);
        $transfer->setDatum($datum);
        $transfer->setIdentifier($identifier);
        $transfer->setSeizoen($seizoen);
        $this->em->persist($transfer);

        $renner->setPloeg($ploegNaar);
        $this->em->persist($renner);
        $transfer->setTransferType($transferType);
        return $transfer;
    }

    public function doUserTransfer(Ploeg $ploeg, Renner $rennerUit, Renner $rennerIn, $seizoen) {
        $identifier = uniqid();
        // behandel de uitgaande transfer eerst
        $transferUit = new Transfer();
        $transferUit->setRenner($rennerUit);
        $transferUit->setPloegVan($ploeg);
        $transferUit->setPloegNaar(null);
        $transferUit->setDatum(new \DateTime());
        $transferUit->setTransferType(Transfer::USERTRANSFER);
        $transferUit->setSeizoen($seizoen);
        $transferUit->setIdentifier($identifier);
        $this->em->persist($transferUit);
        $rennerUit->setPloeg(null);
        // de binnenkomende transfer
        $transferIn = new Transfer();
        $transferIn->setRenner($rennerIn);
        $transferIn->setPloegVan(null);
        $transferIn->setPloegNaar($ploeg);
        $transferIn->setDatum(new \DateTime());
        $transferIn->setTransferType(Transfer::USERTRANSFER);
        $transferIn->setSeizoen($seizoen);
        $transferIn->setIdentifier($identifier);
        $this->em->persist($transferIn);
        $rennerIn->setPloeg($ploeg);
        return true;
    }

}