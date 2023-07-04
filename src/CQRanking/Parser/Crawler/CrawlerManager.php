<?php declare(strict_types=1);

namespace App\CQRanking\Parser\Crawler;

use App\CQRanking\Parser\Exception\CQParserException;
use Symfony\Component\DomCrawler\Crawler;

class CrawlerManager
{
    /**
     * @var Crawler
     */
    private $crawler;

    public function __construct()
    {
        $this->crawler = new Crawler();
    }

    public function getCrawler(string $url): Crawler
    {
        $this->crawler->clear();
        $this->crawler->addContent($this->getContent($url), 'text/html');
        return $this->crawler;
    }

    public function getCrawlerForMatchSelector(string $url)
    {
        return $this->getCrawler($url);
    }

    public function getCrawlerForHTMLContent(string $content): Crawler
    {
        $this->crawler->addContent($content, 'text/html');
        return $this->crawler;
    }

    private function getContent($feed): string
    {
        $content = file_get_contents($feed);
        if (false === $content) {
            throw new CQParserException('Unable to get content from ' . $feed);
        }
        return $content;
    }
}
