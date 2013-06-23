<?php

namespace Cyclear\GameBundle\EntityManager;

use Doctrine\ORM\EntityManager;
use Cyclear\GameBundle\Entity\Transfer,
    Cyclear\GameBundle\Entity\Renner,
    Cyclear\GameBundle\Entity\Ploeg;

class TransferManager
{
    /**
     * 
     * @var EntityManager
     */
    private $em;

    private $contractManager;

    /**
     * 
     * @param EntityManager $em
     * @param Transfer $entity
     */
    public function __construct(EntityManager $em, \Cyclear\GameBundle\EntityManager\ContractManager $contractManager)
    {
        $this->em = $em;
        $this->contractManager = $contractManager;
    }

    public function doDraftTransfer(Transfer $transfer)
    {
        list($renner, $seizoen, $datum) = array($transfer->getRenner(), $transfer->getSeizoen(), $transfer->getDatum());
        try {
            $this->em->beginTransaction();
            $ploeg = $this->em->getRepository("CyclearGameBundle:Renner")->getPloeg($renner, $seizoen);
            if (null !== $ploeg) {
                $releaseTransfer = $this->createReleaseTransfer($transfer, $ploeg);
                $this->contractManager->releaseRenner($renner, $seizoen, $datum);
                $transfer->setInversionTransfer($releaseTransfer);
                $this->em->persist($releaseTransfer);
            }
            $this->contractManager->createContract($renner, $transfer->getPloegNaar(), $seizoen, $datum);
            $this->em->persist($transfer);
            $this->em->commit();
        } catch (Exception $e) {
            $this->em->rollback();
        }
        return true;
    }

    public function createReleaseTransfer(Transfer $transfer, $ploeg)
    {
        $renner = $transfer->getRenner();
        $seizoen = $transfer->getSeizoen();
        $transferNew = new Transfer();
        $transferNew->setRenner($renner);
        $transferNew->setPloegVan($ploeg);
        $transferNew->setPloegNaar(null);
        $datum = clone $transfer->getDatum();
        $transferNew->setDatum($datum);
        $transferNew->setSeizoen($seizoen);
        $transferNew->setTransferType($transfer->getTransferType());
        return $transferNew;
    }

    public function doExchangeTransfer($renner1, $renner2, $datum, $seizoen)
    {
        try {
            $this->em->beginTransaction();
            $t1 = new Transfer();
            $t1->setRenner($renner1);
            $t1->setPloegNaar($this->em->getRepository("CyclearGameBundle:Renner")->getPloeg($renner2, $seizoen));
            $t1->setDatum(clone $datum);
            $t1->setSeizoen($seizoen);
            $t1->setTransferType(Transfer::ADMINTRANSFER);
            $releaseTransfer1 = $this->createReleaseTransfer($t1, $this->em->getRepository("CyclearGameBundle:Renner")->getPloeg($renner1, $seizoen));
            $this->contractManager->releaseRenner($renner1, $seizoen, $datum);

            $t2 = new Transfer();
            $t2->setRenner($renner2);
            $t2->setPloegNaar($this->em->getRepository("CyclearGameBundle:Renner")->getPloeg($renner1, $seizoen));
            $t2->setDatum(clone $datum);
            $t2->setSeizoen($seizoen);
            $t2->setTransferType(Transfer::ADMINTRANSFER);
            $releaseTransfer2 = $this->createReleaseTransfer($t2, $this->em->getRepository("CyclearGameBundle:Renner")->getPloeg($renner2, $seizoen));
            $this->contractManager->releaseRenner($renner2, $seizoen, $datum);

            $t1->setInversionTransfer($t2);
            $t2->setInversionTransfer($t1);

            $this->contractManager->createContract($renner1, $t1->getPloegNaar(), $seizoen, $datum);
            $this->contractManager->createContract($renner2, $t2->getPloegNaar(), $seizoen, $datum);
            
            $this->em->persist($t1);
            $this->em->persist($releaseTransfer1);
            $this->em->persist($t2);
            $this->em->persist($releaseTransfer2);
            
            $this->em->commit();
        } catch (Exception $e) {
            $this->em->rollback();
        }
        return true;
    }

    public function doUserTransfer(Ploeg $ploeg, Renner $rennerUit, Renner $rennerIn, $seizoen)
    {
        try {
            $this->em->beginTransaction();
            $datum = new \DateTime();
            // behandel de uitgaande transfer eerst
            $transferUit = new Transfer();
            $transferUit->setRenner($rennerUit);
            $transferUit->setPloegVan($ploeg);
            $transferUit->setPloegNaar(null);
            $transferUit->setDatum($datum);
            $transferUit->setTransferType(Transfer::USERTRANSFER);
            $transferUit->setSeizoen($seizoen);
            $this->em->persist($transferUit);
            $this->contractManager->releaseRenner($rennerUit, $seizoen, $datum);
            // de binnenkomende transfer
            $transferIn = new Transfer();
            $transferIn->setRenner($rennerIn);
            $transferIn->setPloegVan(null);
            $transferIn->setPloegNaar($ploeg);
            $transferIn->setDatum(new \DateTime());
            $transferIn->setTransferType(Transfer::USERTRANSFER);
            $transferIn->setSeizoen($seizoen);
            $this->em->persist($transferIn);
            $this->contractManager->createContract($rennerIn, $ploeg, $seizoen, $datum);
            //$transferUit->setInversionTransfer($transferIn);
            $transferIn->setInversionTransfer($transferUit);
        } catch (\Exception $e) {
            $this->em->rollback();
        }
        $this->em->commit();
        return true;
    }
}