<?php declare(strict_types=1);

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
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Wedstrijd", inversedBy="uitslagen", cascade={"all"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $wedstrijd;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Renner")
     */
    private $renner;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Ploeg")
     */
    private $ploeg;

    /**
     * @ORM\Column(name="positie", type="smallint")
     */
    private $positie;

    /**
     * @ORM\Column(type="float", name="ploegPunten")
     */
    private $ploegPunten;

    /**
     * @ORM\Column(type="float", name="rennerPunten")
     */
    private $rennerPunten;

    public function getId()
    {
        return $this->id;
    }

    public function setWedstrijd(Wedstrijd $wedstrijd): void
    {
        $this->wedstrijd = $wedstrijd;
    }

    public function getWedstrijd()
    {
        return $this->wedstrijd;
    }

    public function setRenner($renner): void
    {
        $this->renner = $renner;
    }

    public function getRenner()
    {
        return $this->renner;
    }

    public function setPloeg(Ploeg $ploeg = null): void
    {
        $this->ploeg = $ploeg;
    }

    public function getPloeg()
    {
        return $this->ploeg;
    }

    public function setPositie($positie): void
    {
        $this->positie = $positie;
    }

    public function getPositie()
    {
        return $this->positie;
    }

    public function setPloegPunten($punten): void
    {
        $this->ploegPunten = $punten;
    }

    public function getPloegPunten()
    {
        return $this->ploegPunten;
    }

    public function getRennerPunten()
    {
        return $this->rennerPunten;
    }

    public function setRennerPunten($rennerPunten): void
    {
        $this->rennerPunten = $rennerPunten;
    }

    public function __toString()
    {
        return 'uitslag nr ' . $this->getId();
    }
}
