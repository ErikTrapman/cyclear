<?php declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * App\Entity\Ploeg
 *
 * @ORM\Table(name="ploeg")
 * @ORM\Entity(repositoryClass="App\Repository\PloegRepository")
 * @Serializer\ExclusionPolicy("all")
 */
class Ploeg
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @Serializer\Expose
     * @Serializer\Groups({"small","medium"})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="ploeg")
     */
    private $user;

    /**
     * @ORM\Column(name="naam", type="string", length=255)
     *
     * @Serializer\Expose
     * @Serializer\Groups({"small","medium"})
     */
    private $naam;

    /**
     * @ORM\Column(name="afkorting", type="string", length=6)
     *
     * @Serializer\Expose
     * @Serializer\Groups({"small","medium"})
     */
    private $afkorting;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Seizoen")
     */
    private $seizoen;

    private $punten;

    /**
     * @ORM\Column(type="text", nullable=true, length=16777215)
     */
    private $memo;

    public function getUser()
    {
        return $this->user;
    }

    public function setUser($user)
    {
        $this->user = $user;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setNaam($naam)
    {
        $this->naam = $naam;
    }

    public function getNaam()
    {
        return $this->naam;
    }

    public function getAfkorting()
    {
        return $this->afkorting;
    }

    public function setAfkorting($afkorting)
    {
        $this->afkorting = $afkorting;
    }

    public function __toString()
    {
        return (string)$this->getAfkorting();
    }

    /**
     * @return Seizoen
     */
    public function getSeizoen()
    {
        return $this->seizoen;
    }

    public function setSeizoen($seizoen)
    {
        $this->seizoen = $seizoen;
    }

    public function getNaamWithSeizoen()
    {
        return $this->getNaam() . ' [' . $this->getSeizoen()->getIdentifier() . ']';
    }

    /**
     * @Serializer\Groups({"small","medium"})
     * @Serializer\VirtualProperty()
     */
    public function getPunten()
    {
        return $this->punten;
    }

    public function setPunten($punten)
    {
        $this->punten = $punten;
    }

    public function getMemo()
    {
        return $this->memo;
    }

    public function setMemo($memo)
    {
        $this->memo = $memo;
    }
}
