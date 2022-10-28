<?php declare(strict_types=1);

/*
 * This file is part of the Cyclear-game package.
 *
 * (c) Erik Trapman <veggatron@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * App\Entity\Uitslag
 *
 * @ORM\Table(name="uitslag")
 * @ORM\Entity(repositoryClass="App\Repository\UitslagRepository")
 */
class Uitslag
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var object
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Wedstrijd", inversedBy="uitslagen", cascade={"all"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $wedstrijd;

    /**
     * @var Renner
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Renner")
     */
    private $renner;

    /**
     * @var object
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Ploeg")
     */
    private $ploeg;

    /**
     * @var smallint
     *
     * @ORM\Column(name="positie", type="smallint")
     */
    private $positie;

    /**
     * @var float
     *
     * @ORM\Column(type="float", name="ploegPunten")
     */
    private $ploegPunten;

    /**
     * @var float
     *
     * @ORM\Column(type="float", name="rennerPunten")
     */
    private $rennerPunten;

    /**
     * Get id
     *
     * @return int
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
     * @return Renner
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
    public function setPloeg(Ploeg $ploeg = null)
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
        return 'uitslag nr ' . $this->getId();
    }
}
