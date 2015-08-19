<?php

/*
 * This file is part of the Cyclear-game package.
 *
 * (c) Erik Trapman <veggatron@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cyclear\GameBundle\Tests\EntityManager;

use Cyclear\GameBundle\Entity\Transfer;
use Cyclear\GameBundle\Tests\BaseFunctional;
use DateTime;

class TransferManagerTest extends BaseFunctional
{
    /**
     *
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     *
     * @var \Cyclear\GameBundle\EntityManager\TransferManager
     */
    private $transferManager;

    private $ploegRepo;

    private $rennerRepo;

    private $contractRepo;

    private $transferRepo;

    public function setUp()
    {
        $this->em = $this->getContainer()->get('doctrine')->getManager();
        $this->transferManager = $this->getContainer()->get('cyclear_game.manager.transfer');
        $this->ploegRepo = $this->em->getRepository("CyclearGameBundle:Ploeg");
        $this->rennerRepo = $this->em->getRepository("CyclearGameBundle:Renner");
        $this->contractRepo = $this->em->getRepository("CyclearGameBundle:Contract");
        $this->transferRepo = $this->em->getRepository("CyclearGameBundle:Transfer");
    }

    private function getSeizoen()
    {
        return $this->em->getRepository("CyclearGameBundle:Seizoen")->find(1);
    }

    private function createTransfer($renner, $ploeg, $type = Transfer::DRAFTTRANSFER)
    {
        $t = new Transfer();
        $t->setDatum(new DateTime());
        $t->setPloegNaar($ploeg);
        $t->setRenner($renner);
        $t->setSeizoen($this->getSeizoen());
        $t->setTransferType($type);
        return $t;
    }

    public function testDraftTransferAndContractCreation()
    {
        $this->doLoadFixtures();
        $em = $this->em;

        $p1 = $this->ploegRepo->find(1);
        $r1 = $this->rennerRepo->find(1);

        $t = $this->createTransfer($r1, $p1);
        $em->persist($t);

        $this->transferManager->doDraftTransfer($t);

        $em->flush();

        $c = $this->contractRepo->find(1);
        $this->assertEquals($t->getDatum()->format('dmyhi'), $c->getStart()->format('dmyhi'));
        $this->assertEquals($c->getRenner(), $r1);
        $this->assertEquals(null, $c->getEind());
    }

    /**
     * 
     * @dataProvider testRevertExchangeTransfersDataProvider
     */
    public function testRevertExchangeTransfer($revertId1, $revertId2)
    {
        $this->doLoadFixtures();

        $p1 = $this->ploegRepo->find(1);
        $r1 = $this->rennerRepo->find(1);

        $p2 = $this->ploegRepo->find(2);
        $r2 = $this->rennerRepo->find(2);

        $seizoen = $this->getSeizoen();

        $draftTransfer1 = $this->createTransfer($r1, $p1);
        $draftTransfer2 = $this->createTransfer($r2, $p2);
        $this->em->persist($draftTransfer1);
        $this->em->persist($draftTransfer2);
        $this->em->flush();

        $this->transferManager->doDraftTransfer($draftTransfer1);
        $this->transferManager->doDraftTransfer($draftTransfer2);
        $this->em->flush();

        $this->transferManager->doExchangeTransfer($r1, $r2, new DateTime(), $seizoen);
        $this->em->flush();

        $renner1PloegAfterExchange = $this->rennerRepo->getPloeg($r1, $seizoen);
        $renner2PloegAfterExchange = $this->rennerRepo->getPloeg($r2, $seizoen);

        // find the last transfer for rider 1
        $renner1PloegNaarTransfer = $this->transferRepo->find($revertId1);
        $this->transferManager->revertTransfer($renner1PloegNaarTransfer);
        $this->em->flush();

        $renner2PloegNaarTransfer = $this->transferRepo->find($revertId2);
        $this->transferManager->revertTransfer($renner2PloegNaarTransfer);
        $this->em->flush();

        $contracts = $this->contractRepo->findAll();
        $transfers = $this->transferRepo->findAll();
        $this->assertEquals(2, count($transfers));
        $this->assertEquals(2, count($contracts));
        $this->assertEquals($p1, $this->rennerRepo->getPloeg($r1, $seizoen));
        $this->assertEquals($p2, $this->rennerRepo->getPloeg($r2, $seizoen));
        $this->assertEquals(1, $contracts[0]->getId());
        $this->assertEquals(2, $contracts[1]->getId());
        $this->assertEquals(1, $transfers[0]->getId());
        $this->assertEquals(2, $transfers[1]->getId());
    }

    public function testRevertExchangeTransfersDataProvider()
    {
        return array(
            array(6, 4),
            array(5, 3),
            array(3, 5),
            array(4, 6),
        );
    }

    /**
     * @dataProvider revertUserTransfersDataProvider
     */
    public function testRevertUserTransfer($transferIdToRevert)
    {
        $this->doLoadFixtures();

        $p1 = $this->ploegRepo->find(1);
        $r1 = $this->rennerRepo->find(1);

        $r2 = $this->rennerRepo->find(2);

        $seizoen = $this->getSeizoen();

        $draftTransfer1 = $this->createTransfer($r1, $p1, Transfer::DRAFTTRANSFER);
        $this->em->persist($draftTransfer1);
        $this->em->flush();

        $this->transferManager->doDraftTransfer($draftTransfer1);
        $this->em->flush();

        $this->transferManager->doUserTransfer($p1, $r1, $r2, $seizoen);
        $this->em->flush();

        $revertTransfer = $this->transferRepo->find($transferIdToRevert);
        $this->transferManager->revertTransfer($revertTransfer);
        $this->em->flush();

        $transfers = $this->transferRepo->findAll();
        $contracts = $this->contractRepo->findAll();
        $this->assertEquals(1, count($transfers));
        $this->assertEquals(1, count($contracts));
        $this->assertEquals(1, $transfers[0]->getId());
        $this->assertEquals(1, $contracts[0]->getId());
        $this->assertEquals(null, $contracts[0]->getEind());
        $this->assertEquals($p1, $this->rennerRepo->getPloeg($r1, $seizoen));
        $this->assertEquals(null, $this->rennerRepo->getPloeg($r2, $seizoen));
    }

    public function revertUserTransfersDataProvider()
    {
        return array(
            array(3),
            array(2)
        );
    }

    public function testRevertDraftTransfer()
    {
        $this->doLoadFixtures();

        $p1 = $this->ploegRepo->find(1);
        $r1 = $this->rennerRepo->find(1);

        $seizoen = $this->getSeizoen();

        $draftTransfer1 = $this->createTransfer($r1, $p1, Transfer::DRAFTTRANSFER);
        $this->transferManager->doDraftTransfer($draftTransfer1);
        $this->em->flush();

        $this->transferManager->revertTransfer($this->transferRepo->find(1));
        $this->em->flush();

        $this->assertEquals(0, count($this->transferRepo->findAll()));
        $this->assertEquals(0, count($this->contractRepo->findAll()));
        $this->assertEquals(null, $this->rennerRepo->getPloeg($r1, $seizoen));
    }

    public function testDraftTransfer()
    {
        $this->doLoadFixtures();

        $p1 = $this->ploegRepo->find(1);
        $r1 = $this->rennerRepo->find(1);

        $draftTransfer1 = $this->createTransfer($r1, $p1, Transfer::DRAFTTRANSFER);
        $this->transferManager->doDraftTransfer($draftTransfer1);
        $this->em->flush();
        $this->assertEquals(1, count($this->transferRepo->findAll()));
        $this->assertEquals(1, count($this->contractRepo->findAll()));

        $this->assertEquals($p1, $this->rennerRepo->getPloeg($r1, $this->getSeizoen()));
    }

    public function testUserTransfer()
    {
        $this->doLoadFixtures();

        $p1 = $this->ploegRepo->find(1);
        $r1 = $this->rennerRepo->find(1);

        $r2 = $this->rennerRepo->find(2);

        $seizoen = $this->getSeizoen();

        $draftTransfer1 = $this->createTransfer($r1, $p1, Transfer::DRAFTTRANSFER);
        $this->em->persist($draftTransfer1);
        $this->em->flush();

        $this->transferManager->doDraftTransfer($draftTransfer1);
        $this->em->flush();

        $this->transferManager->doUserTransfer($p1, $r1, $r2, $seizoen);
        $this->em->flush();

        $this->assertEquals(3, count($this->transferRepo->findAll()));
        $this->assertEquals(2, count($this->contractRepo->findAll()));
        $this->assertEquals($p1, $this->rennerRepo->getPloeg($r2, $this->getSeizoen()));
        $this->assertEquals(null, $this->rennerRepo->getPloeg($r1, $this->getSeizoen()));
    }

    public function testExchangeTransfer()
    {
        $this->doLoadFixtures();

        $p1 = $this->ploegRepo->find(1);
        $r1 = $this->rennerRepo->find(1);

        $p2 = $this->ploegRepo->find(2);
        $r2 = $this->rennerRepo->find(2);

        $seizoen = $this->getSeizoen();

        $draftTransfer1 = $this->createTransfer($r1, $p1);
        $draftTransfer2 = $this->createTransfer($r2, $p2);
        $this->em->persist($draftTransfer1);
        $this->em->persist($draftTransfer2);
        $this->em->flush();

        $this->transferManager->doDraftTransfer($draftTransfer1);
        $this->transferManager->doDraftTransfer($draftTransfer2);
        $this->em->flush();

        $this->transferManager->doExchangeTransfer($r1, $r2, new DateTime(), $seizoen);
        $this->em->flush();

        $this->assertEquals(6, count($this->transferRepo->findAll()));
        $this->assertEquals(4, count($this->contractRepo->findAll()));

        $this->assertEquals($p2, $this->rennerRepo->getPloeg($r1, $seizoen));
        $this->assertEquals($p1, $this->rennerRepo->getPloeg($r2, $seizoen));
    }

    public function testReleaseTransfersAreCreated()
    {
        
    }

    public function testInversionTransfersAreAsExpected()
    {
        
    }
}