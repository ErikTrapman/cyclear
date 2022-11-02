<?php declare(strict_types=1);

/*
 * This file is part of the CQ-ranking parser package.
 *
 * (c) Erik Trapman <veggatron@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\CQRanking\Parser\Match;

use App\CQRanking\Parser\Crawler\CrawlerManager;
use App\CQRanking\Parser\Match\MatchParser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MatchParserTest extends WebTestCase
{
    public function testMatchParser()
    {
        $client = static::createClient();
        $matchParser = new MatchParser(new CrawlerManager(), 'https://cqranking.com/men/asp/gen/RacesRecent.asp?changed=0');
        $res = $matchParser->getMatches();
        $this->assertEquals(true, !empty($res));
    }
}
