<?php

/*
 * This file is part of the Cyclear-game package.
 *
 * (c) Erik Trapman <veggatron@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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

    public function doExchangeTransfer($renner1, $renner2, $datum, $seizoen, $type = Transfer::ADMINTRANSFER)
    {
        $ploeg1 = $this->em->getRepository("CyclearGameBundle:Renner")->getPloeg($renner1, $seizoen);
        $ploeg2 = $this->em->getRepository("CyclearGameBundle:Renner")->getPloeg($renner2, $seizoen);
        if ($ploeg1 instanceof Ploeg && $ploeg2 instanceof Ploeg) {
            try {

                $this->em->beginTransaction();

                $t1 = new Transfer();
                $t1->setRenner($renner1);
                $t1->setPloegNaar($this->em->getRepository("CyclearGameBundle:Renner")->getPloeg($renner2, $seizoen));
                $t1->setDatum(clone $datum);
                $t1->setSeizoen($seizoen);
                $t1->setTransferType($type);

                $t2 = new Transfer();
                $t2->setRenner($renner2);
                $t2->setPloegNaar($this->em->getRepository("CyclearGameBundle:Renner")->getPloeg($renner1, $seizoen));
                $t2->setDatum(clone $datum);
                $t2->setSeizoen($seizoen);
                $t2->setTransferType($type);

                $releaseTransfer1 = $this->createReleaseTransfer($t1, $ploeg1);
                $this->em->persist($releaseTransfer1);
                $this->contractManager->releaseRenner($renner1, $seizoen, $datum);

                $releaseTransfer2 = $this->createReleaseTransfer($t2, $ploeg2);
                $this->contractManager->releaseRenner($renner2, $seizoen, $datum);
                $this->em->persist($releaseTransfer2);


                $releaseTransfer1->setInversionTransfer($t2);
                $t2->setInversionTransfer($releaseTransfer1);

                $releaseTransfer2->setInversionTransfer($t1);
                $t1->setInversionTransfer($releaseTransfer2);

                $this->contractManager->createContract($renner1, $t1->getPloegNaar(), $seizoen, $datum);
                $this->contractManager->createContract($renner2, $t2->getPloegNaar(), $seizoen, $datum);

                $this->em->persist($t1);
                $this->em->persist($t2);

                $this->em->commit();
            } catch (Exception $e) {
                $this->em->rollback();
                return false;
            }
        } else {
            if ($ploeg1 instanceof Ploeg) {
                $this->doUserTransfer($ploeg1, $renner1, $renner2, $seizoen);
            } elseif ($ploeg2 instanceof Ploeg) {
                $this->doUserTransfer($ploeg2, $renner2, $renner1, $seizoen);
            }
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
            $transferUit->setInversionTransfer($transferIn);
            $this->em->commit();
        } catch (\Exception $e) {
            $this->em->rollback();
        }
        return true;
    }

    public function revertTransfer(Transfer $transfer)
    {
        try {
            $this->em->beginTransaction();
            $this->revertBaseTransfer($transfer);
            if (null !== $transfer->getInversionTransfer()) {
                $this->revertInversionTransfer($transfer->getInversionTransfer());
                $this->em->remove($transfer->getInversionTransfer());
            }
            $this->em->remove($transfer);
            $this->em->commit();
            return true;
        } catch (\Exception $e) {
            $this->em->rollback();
            return false;
        }
    }

    private function revertInversionTransfer($transfer)
    {
        return $this->revertBaseTransfer($transfer);
    }

    private function revertBaseTransfer($transfer)
    {
        $renner = $transfer->getRenner();
        if ($transfer->getPloegNaar()) {
            $ploegNaarContract = $this->em->getRepository("CyclearGameBundle:Contract")->getLastContract($renner, $transfer->getSeizoen(), $transfer->getPloegNaar());
            if ($ploegNaarContract) {
                $this->em->remove($ploegNaarContract);
            }
        }
        if ($transfer->getPloegVan()) {
            $ploegVanContract = $this->em->getRepository("CyclearGameBundle:Contract")->getLastFinishedContract($renner, $transfer->getSeizoen(), $transfer->getPloegVan());
            if ($ploegVanContract) {
                $ploegVanContract->setEind(null);
                $this->em->persist($ploegVanContract);
            }
        }
    }
}