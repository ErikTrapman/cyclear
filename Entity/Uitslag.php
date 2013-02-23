<?php

namespace Cyclear\GameBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Cyclear\GameBundle\Entity\Uitslag
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Cyclear\GameBundle\Entity\UitslagRepository")
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
     * @ORM\ManyToOne(targetEntity="Cyclear\GameBundle\Entity\Wedstrijd", inversedBy="uitslagen", cascade={"all"})
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
     * @ORM\Column(type="float")
     */
    private $ploegPunten;

    /**
     * @var float $punten
     *
     * @ORM\Column(type="float")
     */
    private $rennerPunten;

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
    public function setPloegPunten($punten)
    {
        $this->ploegPunten = $punten;
    }

    /**
     * Get punten
     *
     * @return float 
     */
    public function getPloegPunten()
    {
        return $this->ploegPunten;
    }

    public function getRennerPunten()
    {
        return $this->rennerPunten;
    }

    public function setRennerPunten($rennerPunten)
    {
        $this->rennerPunten = $rennerPunten;
    }

    public function __toString()
    {
        return 'uitslag nr '.$this->getId();
    }
}