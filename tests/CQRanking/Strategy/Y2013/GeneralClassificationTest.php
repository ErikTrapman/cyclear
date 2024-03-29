<?php declare(strict_types=1);

namespace App\Tests\CQRanking\Strategy\Y2013;

use App\CQRanking\Parser\Strategy\Y2013\GeneralClassification;
use App\Tests\CQRanking\Strategy\StrategyTest;

class GeneralClassificationTest extends StrategyTest
{
    public function testResultsParseCorrect(): void
    {
        $url = 'http://cqranking.com/men/asp/gen/race.asp?raceid=24430';
        $strategy = new GeneralClassification();
        $results = $strategy->parseResults($this->getCrawler($url));

        $this->assertEquals(111, count($results));

        $first = $results[0];
        $this->assertEquals([1, 7225, 160], [$first['pos'], $first['cqranking_id'], $first['points']]);
        // cq-ranking has results in different tables
        $break1 = $results[31];
        $this->assertEquals([32, 18, 5], [$break1['pos'], $break1['cqranking_id'], $break1['points']]);
        // cq-ranking has results in different tables
        $break2 = $results[61];
        $this->assertEquals([62, 5056, 0], [$break2['pos'], $break2['cqranking_id'], $break2['points']]);
        // cq-ranking has results in different tables
        $last = $results[110];
        $this->assertEquals([111, 12282, 0], [$last['pos'], $last['cqranking_id'], $last['points']]);
    }
}
