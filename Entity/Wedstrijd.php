<?php

namespace Cyclear\GameBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Cyclear\GameBundle\Entity\Wedstrijd
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Wedstrijd {

    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var datetime $datum
     *
     * @ORM\Column(name="datum", type="datetime")
     */
    private $datum;

    /**
     * @var string $naam
     *
     * @ORM\Column(name="naam", type="string", length=255)
     */
    private $naam;

    /**
     * @var int $uitslagtype
     *
     * @ORM\ManyToOne(targetEntity="Cyclear\GameBundle\Entity\UitslagType")
     */
    private $uitslagtype;
    
    
    
    
    public function __construct(){
        $this->uitslagen = array();
    }
    
    /**
     * Get id
     *
     * @return integer 
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set datum
     *
     * @param datetime $datum
     */
    public function setDatum($datum) {
        $this->datum = $datum;
    }

    /**
     * Get datum
     *
     * @return datetime 
     */
    public function getDatum() {
        return $this->datum;
    }

    /**
     * Set naam
     *
     * @param string $naam
     */
    public function setNaam($naam) {
        $this->naam = $naam;
    }

    /**
     * Get naam
     *
     * @return string 
     */
    public function getNaam() {
        return $this->naam;
    }

    /**
     * Set uitslagtype
     *
     * @param object $uitslagtype
     */
    public function setUitslagtype($uitslagtype) {
        $this->uitslagtype = $uitslagtype;
    }

    /**
     * Get uitslagtype
     *
     * @return object 
     */
    public function getUitslagtype() {
        return $this->uitslagtype;
    }
    
    public function getUitslagen() {
        return $this->uitslagen;
    }


    
    public function __toString() {
        return $this->getNaam();
    }

}