<?php declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\Periode;
use App\Entity\Transfer;
use App\Tests\Support\GameWebTestCase;

/**
 * The period standings, on the homepage and on the periods page.
 */
class PeriodTest extends GameWebTestCase
{
    /**
     * position, naam, afkorting twice, period points [transfer points minus lost draft points], transfers, wins.
     */
    private const PL1_ROW = ['01', 'Ploeg 1', 'pl1', 'pl1', '20 [+14]', '1', '1'];

    private const PL2_ROW = ['02', 'Ploeg 2', 'pl2', 'pl2', '0 [0]', '0', '0'];

    private int $periodeId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reload();
        $periode = new Periode();
        $periode->setStart(new \DateTime('-7 days'));
        $periode->setEind(new \DateTime('+7 days'));
        $periode->setSeizoen($this->seizoen);
        $this->em->persist($periode);

        $drafted = $this->rider('Drafted');
        $in = $this->rider('Transfer In');
        $this->transfer($drafted, $this->pl1, Transfer::DRAFTTRANSFER);
        $this->transfer($in, $this->pl1, Transfer::USERTRANSFER, $drafted);
        // the dropped draft rider still scores, without a team: lost for pl1
        $this->race('Race 1', [[$in, $this->pl1, 20], [$drafted, null, 6]]);
        $this->flushAndClear();
        $this->periodeId = $periode->getId();
    }

    public function testHomepageShowsTheCurrentPeriod(): void
    {
        $table = $this->get('/')->filter('#periodes + .container table');

        $this->assertSame(self::PL1_ROW, $this->cellsOfRowWith($table, 'Ploeg 1'));
        $this->assertSame(self::PL2_ROW, $this->cellsOfRowWith($table, 'Ploeg 2'));
    }

    public function testPeriodsPageShowsThePeriod(): void
    {
        $table = $this->get('/uitslag/periodes/' . $this->periodeId)->filter('table');

        $this->assertSame(self::PL1_ROW, $this->cellsOfRowWith($table, 'Ploeg 1'));
        $this->assertSame(self::PL2_ROW, $this->cellsOfRowWith($table, 'Ploeg 2'));
    }
}
