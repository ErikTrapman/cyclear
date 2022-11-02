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

    /**
     * @param $url
     * @return Crawler
     */
    public function getCrawler($url)
    {
        $this->crawler->clear();
        $this->crawler->addContent($this->getContent($url), 'text/html');
        return $this->crawler;
    }

    /**
     * @param type $url
     * @return Crawler Description
     */
    public function getCrawlerForMatchSelector($url)
    {
        return $this->getCrawler($url);
    }

    /**
     * @param $content
     * @return Crawler
     */
    public function getCrawlerForHTMLContent($content)
    {
        $this->crawler->addContent($content, 'text/html');
        return $this->crawler;
    }

    /**
     * @param $feed
     * @return string
     */
    private function getContent($feed)
    {
        $content = file_get_contents($feed);
        if ($content === false) {
            throw new CQParserException('Unable to get content from ' . $feed);
        }
        return $content;
    }
}
