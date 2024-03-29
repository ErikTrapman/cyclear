<?php declare(strict_types=1);

namespace App\Tests\CQRanking\Strategy\Y2013;

use App\CQRanking\Parser\Strategy\Y2013\OneDay;
use App\Tests\CQRanking\Strategy\StrategyTest;

class OneDayTest extends StrategyTest
{
    public function testResultsParseCorrect(): void
    {
        $url = 'http://cqranking.com/men/asp/gen/race.asp?raceid=23598';
        $strategy = new OneDay();
        $results = $strategy->parseResults($this->getCrawler($url));

        $this->assertEquals(115, count($results));

        $first = $results[0];
        $this->assertEquals([1, 73, 280], [$first['pos'], $first['cqranking_id'], $first['points']]);
        // cq-ranking has results in different tables
        $break1 = $results[31];
        $this->assertEquals([32, 3152, 5], [$break1['pos'], $break1['cqranking_id'], $break1['points']]);
        // cq-ranking has results in different tables
        $break2 = $results[61];
        $this->assertEquals([62, 1071, 5], [$break2['pos'], $break2['cqranking_id'], $break2['points']]);
        // cq-ranking has results in different tables
        $last = $results[114];
        $this->assertEquals([115, 7731, 5], [$last['pos'], $last['cqranking_id'], $last['points']]);
    }
}
