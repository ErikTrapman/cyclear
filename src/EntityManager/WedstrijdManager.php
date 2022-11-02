<?php declare(strict_types=1);

namespace App\EntityManager;

use App\CQRanking\Parser\CQParser;

class WedstrijdManager
{
    /**
     * @var CQParser
     */
    private $cqParser;

    public function __construct(CQParser $cqParser)
    {
        $this->cqParser = $cqParser;
    }

    /**
     * @param mixed $crawler
     * @return App\Entity\Wedstrijd
     */
    public function createWedstrijdFromCrawler($crawler, \DateTime $dateTime)
    {
        return $this->createWedstrijd($this->cqParser->getName($crawler), $dateTime);
    }

    /**
     * @param string $name
     * @return App\Entity\Wedstrijd
     */
    public function createWedstrijd($name, \DateTime $datum)
    {
        $wedstrijd = new \App\Entity\Wedstrijd();
        $name = str_replace(['(provisional)', '(prov)'], '', $name);
        $wedstrijd->setNaam($name);
        $wedstrijd->setDatum($datum);
        return $wedstrijd;
    }
}
