<?php declare(strict_types=1);

namespace App\Tests\CQRanking\Strategy;

use App\CQRanking\Parser\Crawler\CrawlerManager;
use PHPUnit\Framework\TestCase;

abstract class StrategyTest extends TestCase
{
    public function getCrawler(string $url)
    {
        $manager = new CrawlerManager();
        return $manager->getCrawler($url);
    }
}
