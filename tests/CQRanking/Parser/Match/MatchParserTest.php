<?php declare(strict_types=1);

namespace App\Tests\CQRanking\Parser\Match;

use App\CQRanking\Parser\Crawler\CrawlerManager;
use App\CQRanking\Parser\Match\MatchParser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MatchParserTest extends WebTestCase
{
    public function testMatchParser(): void
    {
        static::createClient();
        $matchParser = new MatchParser(new CrawlerManager(), 'https://cqranking.com/men/asp/gen/RacesRecent.asp?changed=0');
        $res = $matchParser->getMatches();
        $this->assertEquals(true, !empty($res));
    }
}
