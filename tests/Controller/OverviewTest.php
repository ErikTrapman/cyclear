<?php declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\Ploeg;
use App\Entity\Transfer;
use App\Tests\Support\GameWebTestCase;

/**
 * The standings overview: transfer standings, draft standings and best transfers.
 */
class OverviewTest extends GameWebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->reload();
        // the transfer count column needs season dates
        $this->seizoen->setStart(new \DateTime('-1 month'));
        $this->seizoen->setEnd(new \DateTime('+1 month'));
        $this->flushAndClear();
    }

    public function testTablesShowTransferDraftAndBestTransferPoints(): void
    {
        $this->reload();
        $stayer = $this->rider('Drafted Stayer');
        $leaver = $this->rider('Drafted Leaver');
        $other = $this->rider('Other Draft');
        $in = $this->rider('Transfer In');
        $this->transfer($stayer, $this->pl1, Transfer::DRAFTTRANSFER);
        $this->transfer($leaver, $this->pl1, Transfer::DRAFTTRANSFER);
        $this->transfer($other, $this->pl2, Transfer::DRAFTTRANSFER);
        $this->transfer($in, $this->pl1, Transfer::USERTRANSFER, $leaver);
        // the leaver now scores without a team: lost for pl1
        $this->race('Race 1', [[$stayer, $this->pl1, 10], [$leaver, null, 6], [$in, $this->pl1, 20], [$other, $this->pl2, 4]]);
        $this->flushAndClear();

        $tables = $this->get('/uitslag/overzicht')->filter('table');

        // position, naam, afkorting, points minus lost draft points, gained, lost, transfers
        $this->assertSame(['01', 'Ploeg 1', 'pl1', '14', '+20', '-6', '1'], $this->cellsOfRowWith($tables->eq(0), 'pl1'));
        $this->assertSame(['01', 'Ploeg 2', 'pl2', '0', '+0', '-0', '0'], $this->cellsOfRowWith($tables->eq(0), 'pl2'));
        $this->assertSame(['01', 'Ploeg 1', 'pl1', '16'], $this->cellsOfRowWith($tables->eq(1), 'pl1'));
        $this->assertSame(['02', 'Ploeg 2', 'pl2', '4'], $this->cellsOfRowWith($tables->eq(1), 'pl2'));
        $this->assertSame(['01', 'Ploeg 1', 'pl1', 'Transfer In', '20'], $this->cellsOfRowWith($tables->eq(2), 'pl1'));
        $this->assertCount(2, $tables->eq(2)->filter('tr'));
    }

    public function testQueryCountDoesNotGrowWithTeamsRidersResultsOrTransfers(): void
    {
        $this->get('/uitslag/overzicht');
        $this->addTeamsDraftsTransfersAndRaces(1);
        $queriesForOne = $this->queryCount('/uitslag/overzicht');

        $this->addTeamsDraftsTransfersAndRaces(4);
        $queriesForFive = $this->queryCount('/uitslag/overzicht');

        $this->assertSame($queriesForOne, $queriesForFive);
    }

    private function addTeamsDraftsTransfersAndRaces(int $count): void
    {
        $this->reload();
        for ($i = 0; $i < $count; ++$i) {
            // a new team every time, so per-team queries would show up too
            $team = new Ploeg();
            $team->setNaam('Ploeg ' . $this->riderCount);
            $team->setAfkorting('p' . $this->riderCount);
            $team->setSeizoen($this->seizoen);
            $this->em->persist($team);
            $drafted1 = $this->rider();
            $drafted2 = $this->rider();
            $in = $this->rider();
            $this->transfer($drafted1, $this->pl1, Transfer::DRAFTTRANSFER);
            $this->transfer($drafted2, $team, Transfer::DRAFTTRANSFER);
            $this->transfer($in, $this->pl1, Transfer::USERTRANSFER, $drafted1);
            $this->transfer($this->rider(), $team, Transfer::USERTRANSFER, $drafted2);
            $this->race('Race ' . $this->riderCount, [[$in, $this->pl1], [$drafted1, $this->pl2], [$drafted2, $team]]);
        }
        $this->flushAndClear();
    }
}
