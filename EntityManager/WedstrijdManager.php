<?php

namespace Cyclear\GameBundle\EntityManager;

class WedstrijdManager {

    public function __construct() {
        
    }
    
    /**
     *
     * @param string $url
     * @param \DateTime $dateTime
     * @return Cyclear\GameBundle\Entity\Wedstrijd
     */
    public function createWedstrijdFromUrl( $url, \DateTime $dateTime){
        $parser = new \Cyclear\GameBundle\Parser\CQParser( new \Symfony\Component\DomCrawler\Crawler() );
        return $this->createWedstrijd( $parser->getName($url), $dateTime );
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