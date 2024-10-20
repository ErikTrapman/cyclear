<?php declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Repository\ContractRepository::class)]
#[ORM\Table(name: 'contract')]
class Contract
{
    /**
     * @var int
     */
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Ploeg::class)]
    private $ploeg;

    #[ORM\ManyToOne(targetEntity: Renner::class, inversedBy: 'contracts')]
    private $renner;

    #[ORM\Column(name: 'start', type: 'datetime')]
    private $start;

    #[ORM\Column(name: 'eind', type: 'datetime', nullable: true)]
    private $eind;

    #[ORM\ManyToOne(targetEntity: Seizoen::class)]
    private $seizoen;

    public function getId(): int
    {
        return $this->id;
    }

    public function getPloeg()
    {
        return $this->ploeg;
    }

    public function setPloeg($ploeg): void
    {
        $this->ploeg = $ploeg;
    }

    public function getRenner()
    {
        return $this->renner;
    }

    public function setRenner($renner): void
    {
        $this->renner = $renner;
    }

    public function getStart()
    {
        return $this->start;
    }

    public function setStart(\DateTime $start): void
    {
        $this->start = $start;
    }

    public function getEind()
    {
        return $this->eind;
    }

    public function setEind($eind): void
    {
        $this->eind = $eind;
    }

    public function getSeizoen()
    {
        return $this->seizoen;
    }

    public function setSeizoen($seizoen): void
    {
        $this->seizoen = $seizoen;
    }
}
