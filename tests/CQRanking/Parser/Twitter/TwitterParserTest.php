<?php declare(strict_types=1);

namespace App\Tests\CQRanking\Parser\Twitter;

use App\CQRanking\Parser\Crawler\CrawlerManager;
use App\CQRanking\Parser\Twitter\TwitterParser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TwitterParserTest extends WebTestCase
{
    /**
     * @dataProvider twitterDataProvider
     * @param mixed $cqId
     * @param mixed $exp
     */
    public function testTwitterHandleParser($cqId, $exp): void
    {
        static::createClient();
        $parser = new TwitterParser(new CrawlerManager(), 'https://cqranking.com/men/asp/gen/rider.asp?riderid=');
        $this->assertEquals($exp, $parser->getTwitterHandle($cqId));
    }

    public function twitterDataProvider(): array
    {
        return [
            [16941, 'estecharu'],
            [27, null],
        ];
    }
}
