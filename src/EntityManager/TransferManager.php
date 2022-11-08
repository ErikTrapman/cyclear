<?php declare(strict_types=1);

namespace App\EntityManager;

use App\Entity\Contract;
use App\Entity\Periode;
use App\Entity\Ploeg;
use App\Entity\Renner;
use App\Entity\Seizoen;
use App\Entity\Transfer;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;

class TransferManager
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ContractManager $contractManager,
        private $maxTransfers = null)
    {
    }

    public function doDraftTransfer(Transfer $transfer): bool
    {
        list($renner, $seizoen, $datum) = [$transfer->getRenner(), $transfer->getSeizoen(), $transfer->getDatum()];
        try {
            $this->em->beginTransaction();
            $ploeg = $this->em->getRepository(Renner::class)->getPloeg($renner, $seizoen);
            if (null !== $ploeg) {
                $releaseTransfer = $this->createReleaseTransfer($transfer, $ploeg);
                $this->contractManager->releaseRenner($renner, $seizoen, $datum);
                $transfer->setInversionTransfer($releaseTransfer);
                $this->em->persist($releaseTransfer);
            }
            $this->contractManager->createContract($renner, $transfer->getPloegNaar(), $seizoen, $datum);
            $this->em->persist($transfer);
            $this->em->commit();
        } catch (\Exception $e) {
            $this->em->rollback();
        }
        return true;
    }

    public function createReleaseTransfer(Transfer $transfer, Ploeg $ploeg): Transfer
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

    public function doExchangeTransfer($renner1, $renner2, \DateTime $datum, $seizoen, $type = Transfer::ADMINTRANSFER): bool
    {
        $ploeg1 = $this->em->getRepository(Renner::class)->getPloeg($renner1, $seizoen);
        $ploeg2 = $this->em->getRepository(Renner::class)->getPloeg($renner2, $seizoen);
        if ($ploeg1 instanceof Ploeg && $ploeg2 instanceof Ploeg) {
            try {
                $this->em->beginTransaction();

                $t1 = new Transfer();
                $t1->setRenner($renner1);
                $t1->setPloegNaar($this->em->getRepository(Renner::class)->getPloeg($renner2, $seizoen));
                $t1->setDatum(clone $datum);
                $t1->setSeizoen($seizoen);
                $t1->setTransferType($type);

                $t2 = new Transfer();
                $t2->setRenner($renner2);
                $t2->setPloegNaar($this->em->getRepository(Renner::class)->getPloeg($renner1, $seizoen));
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
            } catch (\Exception $e) {
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

    public function doUserTransfer(Ploeg $ploeg, Renner $rennerUit, Renner $rennerIn, Seizoen $seizoen, $msg = null): bool
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
            $transferIn->setUserComment($msg);
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

    public function revertTransfer(Transfer $transfer): bool
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

    private function revertInversionTransfer($transfer): void
    {
        $this->revertBaseTransfer($transfer);
    }

    private function revertBaseTransfer(Transfer $transfer): void
    {
        $renner = $transfer->getRenner();
        $contractRepo = $this->em->getRepository(Contract::class);
        if ($transfer->getPloegNaar()) {
            $ploegNaarContract = $contractRepo->getLastContract($renner, $transfer->getSeizoen(), $transfer->getPloegNaar());
            if ($ploegNaarContract) {
                $this->em->remove($ploegNaarContract);
            }
        }
        if ($transfer->getPloegVan()) {
            $ploegVanContract = $contractRepo->getLastFinishedContract($renner, $transfer->getSeizoen(), $transfer->getPloegVan());
            if ($ploegVanContract) {
                $ploegVanContract->setEind(null);
                $this->em->persist($ploegVanContract);
            }
        }
    }

    public function getTtlTransfersDoneByPloeg(Ploeg $ploeg)
    {
        $transferTypes = [Transfer::ADMINTRANSFER, Transfer::USERTRANSFER];
        $seizoen = $ploeg->getSeizoen();
        if ($this->maxTransfers) {
            return $this->em->getRepository(Transfer::class)
                ->getTransferCountByType($ploeg, $seizoen->getStart(), $seizoen->getEnd(), $transferTypes);
        } else {
            $periode = $this->em->getRepository(Periode::class)->getCurrentPeriode($seizoen);
            return $this->em->getRepository(Transfer::class)
                ->getTransferCountByType($ploeg, $periode->getStart(), $periode->getEind(), $transferTypes);
        }
    }

    public function getTtlTransfersAtm(Seizoen $seizoen)
    {
        if ($this->maxTransfers) {
            return $this->maxTransfers;
        } else {
            return $this->em->getRepository(Periode::class)->getCurrentPeriode($seizoen)->getTransfers();
        }
    }
}
