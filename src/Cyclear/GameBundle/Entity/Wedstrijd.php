<?php

/*
 * This file is part of the Cyclear-game package.
 *
 * (c) Erik Trapman <veggatron@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
    
    /**
     *
     * @ORM\OneToMany(targetEntity="Cyclear\GameBundle\Entity\Uitslag", mappedBy="wedstrijd", cascade={"remove"})
     * @ORM\OrderBy({"positie" = "ASC"})
     */
    private $uitslagen;
    
    /**
     * 
     * @ORM\ManyToOne(targetEntity="Cyclear\GameBundle\Entity\Seizoen")
     */
    private $seizoen;
    
    public function __construct(){
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
    
    
    public function getSeizoen() {
        return $this->seizoen;
    }

    public function setSeizoen($seizoen) {
        $this->seizoen = $seizoen;
    }



}