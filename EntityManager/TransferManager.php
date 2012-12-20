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

    /**
     * 
     * @param Renner $renner
     * @param Ploeg $ploegVan
     * @param Ploeg $ploegNaar
     * @param \DateTime $datum
     * @return Transfer
     */
    public function doAdminTransfer(Renner $renner, Ploeg $ploegNaar, \DateTime $datum, $transferType, $seizoen)
    {

        try {
            $this->em->beginTransaction();
            $identifier = uniqid();
            $ploeg = $this->em->getRepository("CyclearGameBundle:Renner")->getPloeg($renner, $seizoen);
            // uitgaand. renner vrijmaken van huidige ploeg
            if (null !== $ploeg) {
                $transfer = new Transfer();
                $transfer->setRenner($renner);
                $transfer->setPloegVan($ploeg);
                $transfer->setPloegNaar(null);
                $transfer->setDatum($datum);
                $transfer->setIdentifier($identifier);
                $transfer->setSeizoen($seizoen);
                $transfer->setTransferType($transferType);
                $this->em->persist($transfer);

                $this->contractManager->releaseRenner($renner, $seizoen, $datum);
            }
            // inkomend. renner naar nieuwe ploeg
            $transfer = new Transfer();
            $transfer->setRenner($renner);
            $transfer->setPloegVan(null);
            $transfer->setPloegNaar($ploegNaar);
            $transfer->setDatum($datum);
            $transfer->setIdentifier($identifier);
            $transfer->setSeizoen($seizoen);
            $transfer->setTransferType($transferType);
            $this->em->persist($transfer);
            $this->contractManager->createContract($renner, $ploegNaar, $seizoen, $datum);

            $this->em->persist($renner);
        } catch (\Exception $e) {
            $this->em->rollback();
        }
        $this->em->commit();
        return true;
    }

    public function doUserTransfer(Ploeg $ploeg, Renner $rennerUit, Renner $rennerIn, $seizoen)
    {
        try {
            $this->em->beginTransaction();
            $identifier = uniqid();
            $datum = new \DateTime();
            // behandel de uitgaande transfer eerst
            $transferUit = new Transfer();
            $transferUit->setRenner($rennerUit);
            $transferUit->setPloegVan($ploeg);
            $transferUit->setPloegNaar(null);
            $transferUit->setDatum($datum);
            $transferUit->setTransferType(Transfer::USERTRANSFER);
            $transferUit->setSeizoen($seizoen);
            $transferUit->setIdentifier($identifier);
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
            $transferIn->setIdentifier($identifier);
            $this->em->persist($transferIn);
            $this->contractManager->createContract($rennerIn, $ploeg, $seizoen, $datum);
        } catch (\Exception $e) {
            $this->em->rollback();
        }
        $this->em->commit();
        return true;
    }
}