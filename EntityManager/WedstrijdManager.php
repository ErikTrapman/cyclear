<?php

/*
 * This file is part of the Cyclear-game package.
 *
 * (c) Erik Trapman <veggatron@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
    public function createWedstrijdFromCrawler( $crawler, \DateTime $dateTime ){
        return $this->createWedstrijd( $this->cqParser->getName($crawler), $dateTime );
    }
    
    
    /**
     * 
     * @param string $name
     * @param \DateTime $datum
     * @return Cyclear\GameBundle\Entity\Wedstrijd
     */
    public function createWedstrijd($name, \DateTime $datum ) {
        $wedstrijd = new \Cyclear\GameBundle\Entity\Wedstrijd();
        $name = str_replace( array('(provisional)','(prov)'), '', $name);
        $wedstrijd->setNaam($name);
        $wedstrijd->setDatum($datum);
        return $wedstrijd;
    }

}