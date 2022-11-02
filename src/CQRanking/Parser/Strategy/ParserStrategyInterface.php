<?php declare(strict_types=1);

namespace App\CQRanking\Parser\Strategy;

use Symfony\Component\DomCrawler\Crawler;

interface ParserStrategyInterface
{
    public function parseResults(Crawler $crawler);
}
