<?php declare(strict_types=1);

namespace App\CQRanking\Parser\RecentRaces;

use App\CQRanking\DataContainer\RecentRaceDataContainer;
use App\CQRanking\Parser\Crawler\CrawlerManager;
use App\CQRanking\Parser\Exception\CQParserException;

class RecentRacesParser
{
    public function __construct(private CrawlerManager $crawlerManager)
    {
    }

    public function getRecentRaces(string $content = null, \DateTime $refDate = null): array
    {
        if (null === $content) {
            $content = @file_get_contents('https://cqranking.com/men/asp/gen/RacesRecent.asp?changed=0');
            if (false === $content) {
                throw new CQParserException('Unable to fetch content for RecentRaces on http://cqranking.com/men/asp/gen/RacesRecent.asp?changed=0');
            }
        }
        $ret = [];
        $crawler = $this->crawlerManager->getCrawlerForHTMLContent($content);
        if (null === $refDate) {
            $refDate = new \DateTime('today, 00:00:00');
        }
        $crawler->filter('table.border tr')->each(function ($node, $i) use (&$ret, $refDate) {
            if (0 === $i) {
                return;
            }
            $row = new RecentRaceDataContainer();
            foreach ($node->filter('td') as $index => $td) {
                if (0 == $index) {
                    continue;
                }
                if (1 == $index) {
                    $row->date = \DateTime::createFromFormat('d/m', trim($td->nodeValue));
                    $row->date->setTime(0, 0, 0);
                    // we only know the d+m, we assume that if the date is > today, we have a date in the past.
                    if ($row->date > $refDate) {
                        $row->date->modify('-1 year');
                    }
                }
                if (3 == $index) {
                    $row->category = trim($td->nodeValue);
                }
                if (7 === $index) {
                    $row->name = trim($td->nodeValue);
                    $aEl = $td->getElementsByTagName('a')->item(0);
                    //$a = 'http://cqranking.com/men/asp/gen/' . ;
                    $row->url = $aEl->getAttribute('href');
                }
            }
            $ret[] = $row;
        });
        return array_reverse($ret);
    }
}
