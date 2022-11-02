<?php declare(strict_types=1);

namespace App\CQRanking\Parser;

use Symfony\Component\DomCrawler\Crawler;

class CQParser
{
    /**
     * Enter description here ...
     */
    public function __construct()
    {
    }

    /**
     * Naam van de uitslag.
     */
    public function getName(Crawler $crawler)
    {
        $headers = $crawler->filter('table.borderNoOpac th.raceheader b');
        $values = $headers->extract(['_text', 'b']);
        $name = @$values[0][0];
        if ($name === false) {
            return 'Naam kon niet worden opgehaald. Vul zelf in.';
        }
        return trim($name);
    }

    /**
     * Parse de resultaatregels
     * @param mixed $strategyClassname
     * @return array
     */
    public function getResultRows(Crawler $crawler, $strategyClassname)
    {
        return $strategyClassname->parseResults($crawler);
    }
}
