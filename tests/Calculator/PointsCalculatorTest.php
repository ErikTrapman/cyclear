<?php declare(strict_types=1);

namespace App\Tests\Calculator;

use App\Calculator\PointsCalculator;
use App\Entity\Renner;
use App\Entity\Seizoen;
use App\Entity\Transfer;
use App\Repository\TransferRepository;
use App\Repository\UitslagRepository;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PointsCalculatorTest extends WebTestCase
{
    /**
     * @return MockObject[]
     */
    private function getMocks(): array
    {
        $repo = $this->getMockBuilder(TransferRepository::class)->disableOriginalConstructor()->getMock();
        $repo2 = $this->getMockBuilder(UitslagRepository::class)->disableOriginalConstructor()->getMock();

        return [$repo, $repo2];
    }

    public function testNeverBeenTransfered(): void
    {
        list($transferRepo, $uitslagRepo) = $this->getMocks();
        $transferRepo->expects($this->any())->method('findLastTransferForDate')->will($this->returnValue(null));

        $c = new PointsCalculator($transferRepo, $uitslagRepo);
        $res = $c->canGetTeamPoints(new Renner(), new \DateTime(), new Seizoen());
        $this->assertEquals(false, $res);
    }

    public function testTransferBeforeCourse(): void
    {
        list($transferRepo, $uitslagRepo) = $this->getMocks();
        $t = new Transfer();
        $t->setDatum(new \DateTime('2013-04-30 23:59:59'));
        $transferRepo->expects($this->any())->method('findLastTransferForDate')->will($this->returnValue($t));

        $seizoen = new Seizoen();

        $c = new PointsCalculator($transferRepo, $uitslagRepo);
        $res = $c->canGetTeamPoints(new Renner(), new \DateTime('2013-05-01 11:00:00'), $seizoen);
        $this->assertEquals(true, $res);
    }

    public function testTransferOnCourse(): void
    {
        list($transferRepo, $uitslagRepo) = $this->getMocks();
        $t = new Transfer();
        $t->setDatum(new \DateTime('2013-05-01 09:38'));
        $transferRepo->expects($this->any())->method('findLastTransferForDate')->will($this->returnValue($t));

        $seizoen = new Seizoen();
        $c = new PointsCalculator($transferRepo, $uitslagRepo);
        $res = $c->canGetTeamPoints(new Renner(), new \DateTime('2013-05-01 11:00:00'), $seizoen);
        $this->assertEquals(false, $res);
    }

    public function testTransferAfterCourse(): void
    {
        list($transferRepo, $uitslagRepo) = $this->getMocks();
        $t = new Transfer();
        $t->setDatum(new \DateTime('2013-05-01 09:38'));
        $transferRepo->expects($this->any())->method('findLastTransferForDate')->will($this->returnValue($t));

        $seizoen = new Seizoen();
        $c = new PointsCalculator($transferRepo, $uitslagRepo);
        $t->setDatum(clone $t->getDatum()->modify('+12 hours'));
        $res = $c->canGetTeamPoints(new Renner(), new \DateTime('2013-05-01 11:00:00'), $seizoen);
        $this->assertEquals(false, $res);
    }

    public function testTransferBeforeReferentionCourse(): void
    {
        list($transferRepo, $uitslagRepo) = $this->getMocks();
        $t = new Transfer();
        $t->setDatum(new \DateTime('2013-04-30 23:59:59'));
        $transferRepo->expects($this->exactly(2))->method('findLastTransferForDate')->will($this->returnValue($t));

        $c = new PointsCalculator($transferRepo, $uitslagRepo);
        $res = $c->canGetTeamPoints(new Renner(), new \DateTime('2013-05-21 11:00:00'), new Seizoen(), new \DateTime('2013-05-01 11:00:00'));
        $this->assertEquals(true, $res);
    }

    public function testTransferBeforeReferentionCourseAndDuring(): void
    {
        list($transferRepo, $uitslagRepo) = $this->getMocks();
        $t = new Transfer();
        $t->setDatum(new \DateTime('2013-04-30 23:59:59'));

        $t2 = clone $t;
        $t2->setDatum($t2->getDatum()->modify('+4 days'));
        $transferRepo->expects($this->exactly(2))->method('findLastTransferForDate')->willReturnOnConsecutiveCalls($this->returnValue($t), $this->returnValue($t2));

        $c = new PointsCalculator($transferRepo, $uitslagRepo);
        $res = $c->canGetTeamPoints(new Renner(), new \DateTime('2013-05-21 11:00:00'), new Seizoen(), new \DateTime('2013-05-01 11:00:00'));
        $this->assertEquals(false, $res);
    }

    public function testTransferOnFirstDayOfReferentionCourse(): void
    {
        list($transferRepo, $uitslagRepo) = $this->getMocks();
        $t = new Transfer();
        $t->setDatum(new \DateTime('2016-02-16 00:00:00'));
        $transferRepo->expects($this->any())->method('findLastTransferForDate')->will($this->returnValue($t));

        $c = new PointsCalculator($transferRepo, $uitslagRepo);
        $res = $c->canGetTeamPoints(new Renner(), new \DateTime('2016-02-21 00:00:00'), new Seizoen(), new \DateTime('2013-02-16 00:00:00'));
        $this->assertEquals(false, $res);
    }

    public function testRiderPassedMaxSeasonalPoints(): void
    {
        $transferRepo = $this->getMockBuilder(TransferRepository::class)->disableOriginalConstructor()->getMock();
        $uitslagRepo = $this->getMockBuilder(UitslagRepository::class)->disableOriginalConstructor()->getMock();

        // setup a valid transfer so it will get us points
        $t = new Transfer();
        $t->setDatum(new \DateTime('2013-04-30 23:59:59'));
        $transferRepo->method('findLastTransferForDate')->willReturn($t);
        $uitslagRepo->method('getTotalPuntenForRenner')->willReturn(100);

        $c = new PointsCalculator($transferRepo, $uitslagRepo);
        $seizoen = new Seizoen();
        $seizoen->setMaxPointsPerRider(99);

        $this->assertFalse($c->canGetTeamPoints(new Renner(), new \DateTime('2013-05-01 11:00:00'), $seizoen));
    }
}
