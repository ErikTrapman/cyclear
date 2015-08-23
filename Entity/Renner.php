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
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as Serializer;

/**
 * Cyclear\GameBundle\Entity\Renner
 *
 * @ORM\Table(name="Renner")
 * @ORM\Entity(repositoryClass="Cyclear\GameBundle\Entity\RennerRepository")
 * @Serializer\ExclusionPolicy("all")
 */
class Renner
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @Serializer\Expose
     * @Serializer\Groups({"small","medium"})
     */
    private $id;

    /**
     * @var string $naam
     *
     * @ORM\Column(name="naam", type="string", length=255)
     * @Serializer\Expose
     * @Serializer\Accessor(getter="__toString")
     * @Serializer\Groups({"small","medium"})
     */
    private $naam;

    /**
     * @ORM\Column(name="cqranking_id", type="integer", length=11, nullable=true, unique=true)
     *
     */
    private $cqranking_id;

    /**
     * @ORM\OneToMany(targetEntity="Cyclear\GameBundle\Entity\Transfer", mappedBy="renner")
     * @ORM\OrderBy({"id" = "DESC"}))
     */
    private $transfers;

    /**
     *
     * @ORM\ManyToOne(targetEntity="Cyclear\GameBundle\Entity\Country")
     * @Serializer\Expose
     * @Serializer\Groups({"small","medium"})
     * @Serializer\Accessor(getter="getCountryIso")
     * @Serializer\Type("string")
     */
    private $country;

    /**
     * @Gedmo\Slug(fields={"naam"}, updatable=true)
     * @ORM\Column(length=128, unique=true, nullable=true)
     * @Serializer\Expose
     * @Serializer\Groups({"small","medium"})
     */
    private $slug;

    /**
     * @ORM\Column(nullable=true)
     */
    private $twitter;

    /**
     * @ORM\OneToMany(targetEntity="Cyclear\GameBundle\Entity\Contract", mappedBy="renner")
     * @ORM\OrderBy({"id" = "DESC"}))
     */
    private $contracts;

    public function __construct()
    {
        $this->transfers = new \Doctrine\Common\Collections\ArrayCollection();
    }

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
     * Set naam
     *
     * @param string $naam
     */
    public function setNaam($naam)
    {
        $this->naam = $naam;
    }

    /**
     * Get naam
     *
     * @return string
     */
    public function getNaam()
    {
        return $this->naam;
    }

    public function getCqRanking_id()
    {
        return $this->cqranking_id;
    }

    public function setCqRanking_id($id)
    {
        $this->cqranking_id = $id;
    }

    public function getCqRankingId()
    {
        return $this->getCqRanking_id();
    }

    public function setCqRankingId($id)
    {
        $this->setCqRanking_id($id);
    }

    public function getTransfers()
    {
        return $this->transfers;
    }

    public function setTransfers($transfers)
    {
        $this->transfers = $transfers;
    }

    public function getCountry()
    {
        return $this->country;
    }

    public function setCountry($country)
    {
        $this->country = $country;
    }

    public function __toString()
    {
        $m = new \Cyclear\GameBundle\EntityManager\RennerManager();
        return $m->getRennerSelectorTypeStringFromRenner($this);
    }

    public function getSlug()
    {
        return $this->slug;
    }

    public function setSlug($slug)
    {
        $this->slug = $slug;
    }

    public function getCountryIso()
    {
        return strtolower($this->getCountry()->getIso2());
    }

    public function getContracts()
    {
        return $this->contracts;
    }

    public function setContracts($contracts)
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

    /**
     * @return mixed
     */
    public function getTwitter()
    {
        return $this->twitter;
    }

    /**
     * @param mixed $twitter
     */
    public function setTwitter($twitter)
    {
        $this->twitter = $twitter;
    }

}