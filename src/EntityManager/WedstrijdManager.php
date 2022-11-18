<?php declare(strict_types=1);

namespace App\EntityManager;

use App\CQRanking\Parser\CQParser;
use App\Entity\Wedstrijd;

class WedstrijdManager
{
    public function __construct(private readonly CQParser $cqParser)
    {
    }

    public function createWedstrijdFromCrawler($crawler, \DateTime $dateTime): Wedstrijd
    {
        return $this->createWedstrijd($this->cqParser->getName($crawler), $dateTime);
    }

    public function createWedstrijd($name, \DateTime $datum): Wedstrijd
    {
        $wedstrijd = new Wedstrijd();
        $name = str_replace(['(provisional)', '(prov)'], '', $name);
        $wedstrijd->setNaam($name);
        $wedstrijd->setDatum($datum);
        return $wedstrijd;
    }
}
