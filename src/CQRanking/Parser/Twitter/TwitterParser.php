<?php declare(strict_types=1);

namespace App\CQRanking\Parser\Twitter;

use App\CQRanking\Parser\Crawler\CrawlerManager;

class TwitterParser
{
    public function __construct(
        private readonly CrawlerManager $crawlerManager,
        private readonly string $baseUrl,
    ) {
    }

    public function getTwitterHandle($cqId): ?string
    {
        $crawler = $this->crawlerManager->getCrawler($this->baseUrl . $cqId);
        return $crawler->filter('table.borderNoOpac')->filterXPath('table[1]')->filter('a')->first()->getNode(0)?->nodeValue;
    }
}
