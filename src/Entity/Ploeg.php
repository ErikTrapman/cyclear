<?php declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Repository\PloegRepository::class)]
#[ORM\Table(name: 'ploeg')]
class Ploeg
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private $id;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'ploeg')]
    private $user;

    #[ORM\Column(name: 'naam', type: 'string', length: 255)]
    private $naam;

    #[ORM\Column(name: 'afkorting', type: 'string', length: 6)]
    private $afkorting;

    #[ORM\ManyToOne(targetEntity: Seizoen::class)]
    private $seizoen;

    private $punten;

    #[ORM\Column(type: 'text', nullable: true, length: 16777215)]
    private $memo;

    public function getUser()
    {
        return $this->user;
    }

    public function setUser($user): void
    {
        $this->user = $user;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setNaam(string $naam): void
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

    public function setAfkorting(string $afkorting): void
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

    public function setSeizoen($seizoen): void
    {
        $this->seizoen = $seizoen;
    }

    public function getNaamWithSeizoen(): string
    {
        return $this->getNaam() . ' [' . $this->getSeizoen()->getIdentifier() . ']';
    }

    public function getPunten()
    {
        return $this->punten;
    }

    public function setPunten($punten): void
    {
        $this->punten = $punten;
    }

    public function getMemo()
    {
        return $this->memo;
    }

    public function setMemo($memo): void
    {
        $this->memo = $memo;
    }
}
