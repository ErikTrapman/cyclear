<?php declare(strict_types=1);

namespace App\Tests\CQRanking\Strategy\Y2013;

use App\CQRanking\Parser\Strategy\Y2013\Stage;
use App\Tests\CQRanking\Strategy\StrategyTest;

class StageTest extends StrategyTest
{
    public function testResultsFromGTParseCorrect(): void
    {
        $url = 'http://cqranking.com/men/asp/gen/race.asp?raceid=24019';
        $strategy = new Stage();
        $results = $strategy->parseResults($this->getCrawler($url));

        $this->assertEquals(179, count($results));

        $leader = $results[0];
        $this->assertEquals(['leader', 1994, 18], [$leader['pos'], $leader['cqranking_id'], $leader['points']]);
        $first = $results[1];
        $this->assertEquals([1, 1992, 70], [$first['pos'], $first['cqranking_id'], $first['points']]);
        $last = $results[178];
        $this->assertEquals([178, 13228, 0], [$last['pos'], $last['cqranking_id'], $last['points']]);
    }

    public function testResultsFrom2HCParseCorrect(): void
    {
        $url = 'http://cqranking.com/men/asp/gen/race.asp?raceid=24197';
        $strategy = new Stage();
        $results = $strategy->parseResults($this->getCrawler($url));
        $this->assertEquals(21, count($results));

        $leader = $results[0];
        $this->assertEquals(['leader', 12335, 8], [$leader['pos'], $leader['cqranking_id'], $leader['points']]);
        $first = $results[1];
        $this->assertEquals([1, 12335, 25], [$first['pos'], $first['cqranking_id'], $first['points']]);
        $last = $results[20];
        $this->assertEquals([20, 8768, 0], [$last['pos'], $last['cqranking_id'], $last['points']]);
    }

    public function testResultsFrom21ParseCorrect(): void
    {
        $url = 'http://cqranking.com/men/asp/gen/race.asp?raceid=24460';
        $strategy = new Stage();
        $results = $strategy->parseResults($this->getCrawler($url));
        $this->assertEquals(21, count($results));

        $leader = $results[0];
        $this->assertEquals(['leader', 1058, 6], [$leader['pos'], $leader['cqranking_id'], $leader['points']]);
        $first = $results[1];
        $this->assertEquals([1, 1058, 20], [$first['pos'], $first['cqranking_id'], $first['points']]);
        $last = $results[20];
        $this->assertEquals([20, 9828, 0], [$last['pos'], $last['cqranking_id'], $last['points']]);
    }

    public function testResultsFromWTParseCorrect(): void
    {
        $url = 'http://cqranking.com/men/asp/gen/race.asp?raceid=23853';
        $strategy = new Stage();
        $results = $strategy->parseResults($this->getCrawler($url));
        $this->assertEquals(133, count($results));

        $leader = $results[0];
        $this->assertEquals(['leader', 2086, 9], [$leader['pos'], $leader['cqranking_id'], $leader['points']]);
        $first = $results[1];
        $this->assertEquals([1, 2086, 35], [$first['pos'], $first['cqranking_id'], $first['points']]);
        $last = $results[132];
        $this->assertEquals([132, 18238, 0], [$last['pos'], $last['cqranking_id'], $last['points']]);
    }
}
