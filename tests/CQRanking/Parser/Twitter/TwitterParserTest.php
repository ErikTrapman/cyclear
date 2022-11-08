<?php declare(strict_types=1);

/*
 * This file is part of the CQ-ranking parser package.
 *
 * (c) Erik Trapman <veggatron@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\CQRanking\Parser\Twitter;

use App\CQRanking\Parser\Crawler\CrawlerManager;
use App\CQRanking\Parser\Twitter\TwitterParser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TwitterParserTest extends WebTestCase
{
    /**
     * @dataProvider twitterDataProvider
     *
     * @param mixed $cqId
     * @param mixed $exp
     */
    public function testTwitterHandleParser($cqId, $exp): void
    {
        static::createClient();
        $parser = new TwitterParser(new CrawlerManager(), 'https://cqranking.com/men/asp/gen/rider.asp?riderid=');
        $this->assertEquals($exp, $parser->getTwitterHandle($cqId));
    }

    /**
     * @return (int|null|string)[][]
     *
     * @psalm-return array{0: array{0: 16941, 1: 'estecharu'}, 1: array{0: 27, 1: null}}
     */
    public function twitterDataProvider(): array
    {
        return [
            [16941, 'estecharu'],
            [27, null],
        ];
    }
}
