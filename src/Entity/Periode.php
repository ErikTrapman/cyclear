<?php declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Repository\PeriodeRepository::class)]
#[ORM\Table(name: 'periode')]
class Periode
{
    /**
     * @var int
     */
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private $id;

    #[ORM\Column(name: 'start', type: 'date')]
    private $start;

    #[ORM\Column(name: 'eind', type: 'date')]
    private $eind;

    #[ORM\Column(name: 'transfers', type: 'smallint', nullable: true)]
    private $transfers;

    #[ORM\ManyToOne(targetEntity: Seizoen::class)]
    private $seizoen;

    public function getId(): int
    {
        return $this->id;
    }

    public function setStart(\DateTime $start): void
    {
        $this->start = $start;
    }

    public function getStart()
    {
        return $this->start;
    }

    public function setEind(\DateTime $eind): void
    {
        $this->eind = $eind;
    }

    public function getEind()
    {
        return $this->eind;
    }

    public function setTransfers(int $transfers): void
    {
        $this->transfers = $transfers;
    }

    public function getTransfers()
    {
        return $this->transfers;
    }

    public function getSeizoen()
    {
        return $this->seizoen;
    }

    public function setSeizoen($seizoen): void
    {
        $this->seizoen = $seizoen;
    }

    public function getEnd()
    {
        return $this->getEind();
    }
}
