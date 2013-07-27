<?php

namespace Cyclear\GameBundle\Tests\EntityManager;

class TransferManagerTest extends \Liip\FunctionalTestBundle\Test\WebTestCase
{

    public function testDraftTransfer()
    {
        $this->loadFixtures(array());
        
        $em = $this->getContainer()->get('doctrine')->getManager();
        $p1 = new \Cyclear\GameBundle\Entity\Ploeg();
        $p1->setNaam("P1");
        $p1->setAfkorting("p1");
        $em->persist($p1);
        
        $r1 = new \Cyclear\GameBundle\Entity\Renner();
        $r1->setNaam("RENNER Voornaam");
        //$r1->setSlug("renner-voornaam");
        $em->persist($r1);
        
        $s = new \Cyclear\GameBundle\Entity\Seizoen();
        $s->setIdentifier("S1");
        $em->persist($s);
        
        $em->flush();
        
        $transferManager = $this->getContainer()->get('cyclear_game.manager.transfer');
        $t = new \Cyclear\GameBundle\Entity\Transfer();
        $t->setDatum(new \DateTime());
        $t->setPloegNaar($p1);
        $t->setRenner($r1);
        $t->setSeizoen($s);
        $t->setTransferType(\Cyclear\GameBundle\Entity\Transfer::DRAFTTRANSFER);
        
        $em->persist($t);
        
        $transferManager->doDraftTransfer($t);
        
        $em->flush();
        
        $c = $em->getRepository("CyclearGameBundle:Contract")->find(1);
        $this->assertEquals($t->getDatum()->format('dmyhi'), $c->getStart()->format('dmyhi'));
        $this->assertEquals($c->getRenner(), $r1);
        
    }

    
}