<?php declare(strict_types=1);

namespace App\Tests\Calculator;

use App\Calculator\PointsCalculator;
use App\Entity\Renner;
use App\Entity\Seizoen;
use App\Entity\Transfer;
use App\Repository\TransferRepository;
use App\Repository\UitslagRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PointsCalculatorTest extends WebTestCase
{
    private function getMocks(bool $mockTransferRepository = false): array
    {
        $repo = $mockTransferRepository ? $this->createMock(TransferRepository::class) : $this->createStub(TransferRepository::class);
        $repo2 = $this->createStub(UitslagRepository::class);

        return [$repo, $repo2];
    }

    public function testNeverBeenTransfered(): void
    {
        list($transferRepo, $uitslagRepo) = $this->getMocks();
        $transferRepo->method('findLastTransferForDate')->willReturn(null);

        $c = new PointsCalculator($transferRepo, $uitslagRepo);
        $res = $c->canGetTeamPoints(new Renner(), new \DateTime(), new Seizoen());
        $this->assertEquals(false, $res);
    }

    public function testTransferBeforeCourse(): void
    {
        list($transferRepo, $uitslagRepo) = $this->getMocks();
        $t = new Transfer();
        $t->setDatum(new \DateTime('2013-04-30 23:59:59'));
        $transferRepo->method('findLastTransferForDate')->willReturn($t);

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
        $transferRepo->method('findLastTransferForDate')->willReturn($t);

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
        $transferRepo->method('findLastTransferForDate')->willReturn($t);

        $seizoen = new Seizoen();
        $c = new PointsCalculator($transferRepo, $uitslagRepo);
        $t->setDatum(clone $t->getDatum()->modify('+12 hours'));
        $res = $c->canGetTeamPoints(new Renner(), new \DateTime('2013-05-01 11:00:00'), $seizoen);
        $this->assertEquals(false, $res);
    }

    public function testTransferBeforeReferentionCourse(): void
    {
        list($transferRepo, $uitslagRepo) = $this->getMocks(true);
        $t = new Transfer();
        $t->setDatum(new \DateTime('2013-04-30 23:59:59'));
        $transferRepo->expects($this->exactly(2))->method('findLastTransferForDate')->willReturn($t);

        $c = new PointsCalculator($transferRepo, $uitslagRepo);
        $res = $c->canGetTeamPoints(new Renner(), new \DateTime('2013-05-21 11:00:00'), new Seizoen(), new \DateTime('2013-05-01 11:00:00'));
        $this->assertEquals(true, $res);
    }

    public function testTransferBeforeReferentionCourseAndDuring(): void
    {
        list($transferRepo, $uitslagRepo) = $this->getMocks(true);
        $t = new Transfer();
        $t->setDatum(new \DateTime('2013-04-30 23:59:59'));

        $t2 = clone $t;
        $t2->setDatum($t2->getDatum()->modify('+4 days'));
        $transferRepo->expects($this->exactly(2))->method('findLastTransferForDate')->willReturnOnConsecutiveCalls($t, $t2);

        $c = new PointsCalculator($transferRepo, $uitslagRepo);
        $res = $c->canGetTeamPoints(new Renner(), new \DateTime('2013-05-21 11:00:00'), new Seizoen(), new \DateTime('2013-05-01 11:00:00'));
        $this->assertEquals(false, $res);
    }

    public function testTransferOnFirstDayOfReferentionCourse(): void
    {
        list($transferRepo, $uitslagRepo) = $this->getMocks();
        $t = new Transfer();
        $t->setDatum(new \DateTime('2016-02-16 00:00:00'));
        $transferRepo->method('findLastTransferForDate')->willReturn($t);

        $c = new PointsCalculator($transferRepo, $uitslagRepo);
        $res = $c->canGetTeamPoints(new Renner(), new \DateTime('2016-02-21 00:00:00'), new Seizoen(), new \DateTime('2013-02-16 00:00:00'));
        $this->assertEquals(false, $res);
    }

    public function testRiderPassedMaxSeasonalPoints(): void
    {
        $transferRepo = $this->createStub(TransferRepository::class);
        $uitslagRepo = $this->createStub(UitslagRepository::class);

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

    #[\PHPUnit\Framework\Attributes\DataProvider('seasonalPointsDataProvider')]
    public function testRiderCalculatesCorrectTeamPoints(int $current, int $max, int $given, int $expected)
    {
        $transferRepo = $this->createStub(TransferRepository::class);
        $uitslagRepo = $this->createStub(UitslagRepository::class);

        // setup a valid transfer so it will get us points
        $t = new Transfer();
        $t->setDatum(new \DateTime('2013-04-30 23:59:59'));
        $transferRepo->method('findLastTransferForDate')->willReturn($t);
        $uitslagRepo->method('getTotalPuntenForRenner')->willReturn($current);

        $c = new PointsCalculator($transferRepo, $uitslagRepo);
        $seizoen = new Seizoen();
        $seizoen->setMaxPointsPerRider($max);

        $this->assertEquals($expected, $c->calculateRiderTeamPoints(new Renner(), $seizoen, $given));
    }

    public function testRiderCalculatesTeamPointsWithoutSeasonalMax(): void
    {
        $transferRepo = $this->createStub(TransferRepository::class);
        $uitslagRepo = $this->createStub(UitslagRepository::class);
        $uitslagRepo->method('getTotalPuntenForRenner')->willReturn(100);

        $c = new PointsCalculator($transferRepo, $uitslagRepo);

        $this->assertEquals(110, $c->calculateRiderTeamPoints(new Renner(), new Seizoen(), 110));
    }

    public static function seasonalPointsDataProvider()
    {
        return [
            'normal' => [100, 1500, 110, 110],
            'does not pass max' => [100, 150, 65, 50],
            'already over' => [1500, 1500, 110, 0],
        ];
    }
}
