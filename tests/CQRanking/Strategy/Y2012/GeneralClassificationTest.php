<?php declare(strict_types=1);

/*
 * This file is part of the CQ-ranking parser package.
 *
 * (c) Erik Trapman <veggatron@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\CQRanking\Strategy\Y2012;

use App\CQRanking\Parser\Strategy\Y2012\GeneralClassification;
use App\Tests\CQRanking\Strategy\StrategyTest;

class GeneralClassificationTest extends StrategyTest
{
    public function testResultsParseCorrect()
    {
        $url = 'http://cqranking.com/men/asp/gen/race.asp?raceid=21645';
        $strategy = new GeneralClassification();
        $results = $strategy->parseResults($this->getCrawler($url));

        $this->assertEquals(153, count($results));

        $wiggins = $results[0];
        $this->assertEquals(['1', '990', '600'], [$wiggins['pos'], $wiggins['cqranking_id'], $wiggins['points']]);
        // cq-ranking has results in different tables
        $leipheimer = $results[31];
        $this->assertEquals(['32', '84', '45'], [$leipheimer['pos'], $leipheimer['cqranking_id'], $leipheimer['points']]);
        // cq-ranking has results in different tables
        $cherel = $results[61];
        $this->assertEquals(['62', '3371', '20'], [$cherel['pos'], $cherel['cqranking_id'], $cherel['points']]);
        // cq-ranking has results in different tables
        $engoulvent = $results[152];
        $this->assertEquals(['153', '261', '20'], [$engoulvent['pos'], $engoulvent['cqranking_id'], $engoulvent['points']]);
    }
}
