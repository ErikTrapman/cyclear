<?php declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\Transfer;
use App\Tests\Support\GameWebTestCase;
use Symfony\Component\DomCrawler\Crawler;

/**
 * The latest transfers and race results on the homepage, and what they cost in queries.
 */
class HomepageListsTest extends GameWebTestCase
{
    public function testRaceResultsMarkTransferredRidersOnly(): void
    {
        $this->reload();
        $drafted = $this->rider('Drafted Rider');
        $transferred = $this->rider('Transferred Rider');
        $this->transfer($drafted, $this->pl1, Transfer::DRAFTTRANSFER);
        // drafted by the other team, so only a transfer for pl1
        $this->transfer($transferred, $this->pl2, Transfer::DRAFTTRANSFER);
        $this->transfer($transferred, $this->pl1, Transfer::USERTRANSFER);
        $this->race('Race 1', [[$drafted, $this->pl1], [$transferred, $this->pl1]]);
        $this->flushAndClear();

        $rows = $this->get('/')->filter('#uitslagen + .container tr');
        $marked = $rows->reduce(fn (Crawler $row) => $row->filter('.fa-euro')->count() > 0);

        $this->assertStringContainsString('Drafted Rider', implode(' ', $rows->each(fn (Crawler $row) => $row->text())));
        $this->assertCount(1, $marked);
        $this->assertStringContainsString('Transferred Rider', $marked->text());
        $this->assertStringNotContainsString('Drafted Rider', $marked->text());
    }

    public function testLatestTransfersShowRiderInAndOut(): void
    {
        $this->reload();
        $in = $this->rider('Rider In');
        $out = $this->rider('Rider Out');
        $this->transfer($in, $this->pl2, Transfer::USERTRANSFER, $out);
        $this->flushAndClear();

        $rows = $this->get('/')->filter('#transfers + .container tr')
            ->reduce(fn (Crawler $row) => str_contains($row->text(), 'Rider In'));

        $this->assertCount(1, $rows);
        $cells = $rows->filter('td');
        $this->assertSame('pl2', trim($cells->eq(0)->text()));
        $this->assertStringContainsString('Rider In', $cells->eq(1)->text());
        $this->assertStringContainsString('Rider Out', $cells->eq(2)->text());
    }

    public function testQueryCountDoesNotGrowWithRacesResultsOrTransfers(): void
    {
        $this->get('/');
        $this->addRacesAndTransfers(1);
        $queriesForOne = $this->queryCount('/');

        $this->addRacesAndTransfers(4);
        $queriesForFive = $this->queryCount('/');

        $this->assertSame($queriesForOne, $queriesForFive);
    }

    private function addRacesAndTransfers(int $count): void
    {
        $this->reload();
        for ($i = 0; $i < $count; ++$i) {
            $drafted = $this->rider();
            $transferred = $this->rider();
            $this->transfer($drafted, $this->pl1, Transfer::DRAFTTRANSFER);
            $this->transfer($transferred, $this->pl2, Transfer::USERTRANSFER, $this->rider());
            $this->race('Race ' . $this->riderCount, [[$drafted, $this->pl1], [$transferred, $this->pl2], [$this->rider(), null]]);
        }
        $this->flushAndClear();
    }
}
