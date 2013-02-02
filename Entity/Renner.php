<?php

namespace Cyclear\GameBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Cyclear\GameBundle\Entity\Renner
 *
 * @ORM\Table(name="Renner")
 * @ORM\Entity(repositoryClass="Cyclear\GameBundle\Entity\RennerRepository")
 */
class Renner
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
     * @var string $naam
     *
     * @ORM\Column(name="naam", type="string", length=255)
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

    public function __toString()
    {
        $m = new \Cyclear\GameBundle\EntityManager\RennerManager();
        return $m->getRennerSelectorTypeStringFromRenner($this);
    }
}