<?php

namespace Cyclear\GameBundle\EntityManager;

class WedstrijdManager {
    
    
    private $cqParser;
    
    public function __construct($cqParser) {
        $this->cqParser = $cqParser;
    }
    
    /**
     *
     * @param string $url
     * @param \DateTime $dateTime
     * @return Cyclear\GameBundle\Entity\Wedstrijd
     */
    public function createWedstrijdFromCrawler( $crawler, \DateTime $dateTime){
        return $this->createWedstrijd( $this->cqParser->getName($crawler), $dateTime );
    }
    
    
    /**
     * 
     * @param string $name
     * @param \DateTime $datum
     * @return Cyclear\GameBundle\Entity\Wedstrijd
     */
    public function createWedstrijd($name, \DateTime $datum) {
        $wedstrijd = new \Cyclear\GameBundle\Entity\Wedstrijd();
        $wedstrijd->setNaam($name);
        $wedstrijd->setDatum($datum);
        return $wedstrijd;
    }

}