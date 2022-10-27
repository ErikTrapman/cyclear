<?php

/*
 * This file is part of the Cyclear-game package.
 *
 * (c) Erik Trapman <veggatron@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Calculator;

use App\Calculator\PuntenCalculator;
use App\Entity\Renner;
use App\Entity\Seizoen;
use App\Entity\Transfer;
use App\Entity\Uitslag;
use App\Repository\TransferRepository;
use App\Repository\UitslagRepository;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PuntenCalculatorTest extends WebTestCase
{

    private function getMocks()
    {
        $em = $this->getMockBuilder('Doctrine\ORM\EntityManager')->disableOriginalConstructor()->getMock();
        $repo = $this->getMockBuilder('App\Repository\TransferRepository')->disableOriginalConstructor()->getMock();
        $em->expects($this->any())->method('getRepository')->will($this->returnValue($repo));

        return array($em, $repo);
    }

    public function testNeverBeenTransfered()
    {
        list($em, $repo) = $this->getMocks();
        $repo->expects($this->any())->method('findLastTransferForDate')->will($this->returnValue(null));

        $c = new PuntenCalculator($em);
        $res = $c->canGetTeamPoints(new Renner(), new DateTime(), new Seizoen());
        $this->assertEquals(false, $res);
    }

    public function testTransferBeforeCourse()
    {
        list($em, $repo) = $this->getMocks();
        $t = new Transfer();
        $t->setDatum(new DateTime('2013-04-30 23:59:59'));
        $repo->expects($this->any())->method('findLastTransferForDate')->will($this->returnValue($t));

        $uitslagRepo = $this->getMockBuilder(UitslagRepository::class)->disableOriginalConstructor()->getMock();
        $em->expects($this->at(1))->method('getRepository')->with(Uitslag::class)->willReturn($uitslagRepo);
        $seizoen = new Seizoen();

        $c = new PuntenCalculator($em);
        $res = $c->canGetTeamPoints(new Renner(), new DateTime('2013-05-01 11:00:00'), $seizoen);
        $this->assertEquals(true, $res);
    }

    public function testTransferOnCourse()
    {
        list($em, $repo) = $this->getMocks();
        $t = new Transfer();
        $t->setDatum(new DateTime('2013-05-01 09:38'));
        $repo->expects($this->any())->method('findLastTransferForDate')->will($this->returnValue($t));

        $uitslagRepo = $this->getMockBuilder(UitslagRepository::class)->disableOriginalConstructor()->getMock();
        $em->expects($this->at(1))->method('getRepository')->with(Uitslag::class)->willReturn($uitslagRepo);

        $seizoen = new Seizoen();
        $c = new PuntenCalculator($em);
        $res = $c->canGetTeamPoints(new Renner(), new DateTime('2013-05-01 11:00:00'), $seizoen);
        $this->assertEquals(false, $res);
    }

    public function testTransferAfterCourse()
    {
        list($em, $repo) = $this->getMocks();
        $t = new Transfer();
        $t->setDatum(new DateTime('2013-05-01 09:38'));
        $repo->expects($this->any())->method('findLastTransferForDate')->will($this->returnValue($t));

        $uitslagRepo = $this->getMockBuilder(UitslagRepository::class)->disableOriginalConstructor()->getMock();
        $em->expects($this->at(1))->method('getRepository')->with(Uitslag::class)->willReturn($uitslagRepo);

        $seizoen = new Seizoen();
        $c = new PuntenCalculator($em);
        $t->setDatum(clone $t->getDatum()->modify("+12 hours"));
        $res = $c->canGetTeamPoints(new Renner(), new DateTime('2013-05-01 11:00:00'), $seizoen);
        $this->assertEquals(false, $res);
    }

    public function testTransferBeforeReferentionCourse()
    {
        list($em, $repo) = $this->getMocks();
        $t = new Transfer();
        $t->setDatum(new DateTime('2013-04-30 23:59:59'));
        $repo->expects($this->at(0))->method('findLastTransferForDate')->will($this->returnValue($t));
        $repo->expects($this->at(1))->method('findLastTransferForDate')->will($this->returnValue($t));

        $c = new PuntenCalculator($em);
        $res = $c->canGetTeamPoints(new Renner(), new DateTime('2013-05-21 11:00:00'), null, new DateTime('2013-05-01 11:00:00'));
        $this->assertEquals(true, $res);
    }

    public function testTransferBeforeReferentionCourseAndDuring()
    {
        list($em, $repo) = $this->getMocks();
        $t = new Transfer();
        $t->setDatum(new DateTime('2013-04-30 23:59:59'));
        $repo->expects($this->at(0))->method('findLastTransferForDate')->will($this->returnValue($t));

        $t2 = clone $t;
        $t2->setDatum($t2->getDatum()->modify("+4 days"));
        $repo->expects($this->at(1))->method('findLastTransferForDate')->will($this->returnValue($t2));

        $c = new PuntenCalculator($em);
        $res = $c->canGetTeamPoints(new Renner(), new DateTime('2013-05-21 11:00:00'), null, new DateTime('2013-05-01 11:00:00'));
        $this->assertEquals(false, $res);
    }

    public function testTransferOnFirstDayOfReferentionCourse()
    {
        list($em, $repo) = $this->getMocks();
        $t = new Transfer();
        $t->setDatum(new DateTime('2016-02-16 00:00:00'));
        $repo->expects($this->at(0))->method('findLastTransferForDate')->will($this->returnValue($t));
        $repo->expects($this->at(1))->method('findLastTransferForDate')->will($this->returnValue($t));

        $c = new PuntenCalculator($em);
        $res = $c->canGetTeamPoints(new Renner(), new DateTime('2016-02-21 00:00:00'), null, new DateTime('2013-02-16 00:00:00'));
        $this->assertEquals(false, $res);
    }

    public function testRiderPassedMaxSeasonalPoints()
    {
        $em = $this->getMockBuilder('Doctrine\ORM\EntityManager')->disableOriginalConstructor()->getMock();
        $transferRepo = $this->getMockBuilder(TransferRepository::class)->disableOriginalConstructor()->getMock();
        $uitslagRepo = $this->getMockBuilder(UitslagRepository::class)->disableOriginalConstructor()->getMock();

        $em->expects($this->at(0))->method('getRepository')->with(Transfer::class)->willReturn($transferRepo);
        $em->expects($this->at(1))->method('getRepository')->with(Uitslag::class)->willReturn($uitslagRepo);

        // setup a valid transfer so it will get us points
        $t = new Transfer();
        $t->setDatum(new \DateTime('2013-04-30 23:59:59'));
        $transferRepo->method('findLastTransferForDate')->willReturn($t);
        $uitslagRepo->method('getTotalPuntenForRenner')->willReturn(100);

        $c = new PuntenCalculator($em);
        $seizoen = new Seizoen();
        $seizoen->setMaxPointsPerRider(99);

        $this->assertFalse($c->canGetTeamPoints(new Renner(), new \DateTime('2013-05-01 11:00:00'), $seizoen));
    }
}
