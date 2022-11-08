<?php declare(strict_types=1);

/*
 * This file is part of the CQ-ranking parser package.
 *
 * (c) Erik Trapman <veggatron@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\CQRanking\Strategy;

use App\CQRanking\Parser\Crawler\CrawlerManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DomCrawler\Crawler;

abstract class StrategyTest extends TestCase
{
    public function getCrawler(string $url)
    {
        $manager = new CrawlerManager();
        return $manager->getCrawler($url);
    }
}
