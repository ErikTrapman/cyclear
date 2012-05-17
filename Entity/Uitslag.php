<?php

namespace Cyclear\GameBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Cyclear\GameBundle\Entity\Uitslag
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Uitslag
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var object $wedstrijd
     *
     * @ORM\ManyToOne(targetEntity="Cyclear\GameBundle\Entity\Wedstrijd")
     */
    private $wedstrijd;

    /**
     * @var object $renner
     *
     * @ORM\ManyToOne(targetEntity="Cyclear\GameBundle\Entity\Renner")
     */
    private $renner;

    /**
     * @var object $ploeg
     *
     * @ORM\ManyToOne(targetEntity="Cyclear\GameBundle\Entity\Ploeg")
     */
    private $ploeg;

    /**
     * @var smallint $positie
     *
     * @ORM\Column(name="positie", type="smallint")
     */
    private $positie;

    /**
     * @var float $punten
     *
     * @ORM\Column(name="punten", type="float")
     */
    private $punten;

    /**
     * @var datetime $datum
     *
     * @ORM\Column(name="datum", type="datetime")
     */
    private $datum;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set wedstrijd
     *
     * @param object $wedstrijd
     */
    public function setWedstrijd($wedstrijd)
    {
        $this->wedstrijd = $wedstrijd;
    }

    /**
     * Get wedstrijd
     *
     * @return object 
     */
    public function getWedstrijd()
    {
        return $this->wedstrijd;
    }

    /**
     * Set renner
     *
     * @param object $renner
     */
    public function setRenner($renner)
    {
        $this->renner = $renner;
    }

    /**
     * Get renner
     *
     * @return object 
     */
    public function getRenner()
    {
        return $this->renner;
    }

    /**
     * Set ploeg
     *
     * @param object $ploeg
     */
    public function setPloeg($ploeg)
    {
        $this->ploeg = $ploeg;
    }

    /**
     * Get ploeg
     *
     * @return object 
     */
    public function getPloeg()
    {
        return $this->ploeg;
    }

    /**
     * Set positie
     *
     * @param smallint $positie
     */
    public function setPositie($positie)
    {
        $this->positie = $positie;
    }

    /**
     * Get positie
     *
     * @return smallint 
     */
    public function getPositie()
    {
        return $this->positie;
    }

    /**
     * Set punten
     *
     * @param float $punten
     */
    public function setPunten($punten)
    {
        $this->punten = $punten;
    }

    /**
     * Get punten
     *
     * @return float 
     */
    public function getPunten()
    {
        return $this->punten;
    }

    /**
     * Set datum
     *
     * @param datetime $datum
     */
    public function setDatum($datum)
    {
        $this->datum = $datum;
    }

    /**
     * Get datum
     *
     * @return datetime 
     */
    public function getDatum()
    {
        return $this->datum;
    }

    public function __toString(){
    	return 'uitslag nr '.$this->getId();
    }
    
}