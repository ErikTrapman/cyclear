<?php declare(strict_types=1);

namespace App\CQRanking\Parser;

use App\CQRanking\Parser\Strategy\ParserStrategyInterface;
use Symfony\Component\DomCrawler\Crawler;

class CQParser
{
    public function getName(Crawler $crawler): string
    {
        $headers = $crawler->filter('table.borderNoOpac th.raceheader b');
        $values = $headers->extract(['_text', 'b']);
        $name = @$values[0][0];
        if (false === $name) {
            return 'Naam kon niet worden opgehaald. Vul zelf in.';
        }
        return trim($name);
    }

    public function getResultRows(Crawler $crawler, ParserStrategyInterface $strategyClassname): array
    {
        return $strategyClassname->parseResults($crawler);
    }
}
