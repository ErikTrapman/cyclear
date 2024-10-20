<?php declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation\Slug;

#[ORM\Entity(repositoryClass: \App\Repository\RennerRepository::class)]
#[ORM\Table(name: 'renner')]
class Renner
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private $id;

    #[ORM\Column(name: 'naam', type: 'string', length: 255)]
    private $naam;

    #[ORM\Column(name: 'cqranking_id', type: 'integer', length: 11, nullable: true, unique: true)]
    private $cqranking_id;

    #[ORM\OneToMany(targetEntity: Transfer::class, mappedBy: 'renner')]
    #[ORM\OrderBy(['id' => 'DESC'])]
    private $transfers;

    #[ORM\ManyToOne(targetEntity: Country::class, fetch: 'EAGER')]
    private $country;

    #[Slug(fields: ['naam'], updatable: true)]
    #[ORM\Column(length: 128, unique: true, nullable: true)]
    private $slug;

    #[ORM\Column(nullable: true)]
    private $twitter;

    #[ORM\OneToMany(targetEntity: Contract::class, mappedBy: 'renner')]
    #[ORM\OrderBy(['id' => 'DESC'])]
    private $contracts;

    public function __construct()
    {
        $this->transfers = new \Doctrine\Common\Collections\ArrayCollection();
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

    public function getCqRanking_id()
    {
        return $this->cqranking_id;
    }

    public function setCqRanking_id($id): void
    {
        $this->cqranking_id = $id;
    }

    public function getCqRankingId()
    {
        return $this->getCqRanking_id();
    }

    public function setCqRankingId($id): void
    {
        $this->setCqRanking_id($id);
    }

    public function getTransfers()
    {
        return $this->transfers;
    }

    public function setTransfers($transfers): void
    {
        $this->transfers = $transfers;
    }

    public function getCountry()
    {
        return $this->country;
    }

    public function setCountry($country): void
    {
        $this->country = $country;
    }

    public function __toString()
    {
        $m = new \App\EntityManager\RennerManager();
        return $m->getRennerSelectorTypeStringFromRenner($this);
    }

    public function getSlug()
    {
        return $this->slug;
    }

    public function setSlug(string $slug): void
    {
        $this->slug = $slug;
    }

    public function getCountryIso(): string
    {
        return strtolower($this->getCountry()->getIso2());
    }

    public function getContracts()
    {
        return $this->contracts;
    }

    public function setContracts($contracts): void
    {
        $this->contracts = $contracts;
    }

    public function getLatestContract()
    {
        $c = $this->getContracts();
        if ($c->first()) {
            return $c->first();
        }
        return null;
    }

    public function getTwitter()
    {
        return $this->twitter;
    }

    public function setTwitter(?string $twitter): void
    {
        $this->twitter = $twitter;
    }
}
