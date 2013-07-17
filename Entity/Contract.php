<?php

namespace Cyclear\GameBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Entity(repositoryClass="Cyclear\GameBundle\Entity\ContractRepository")
 */
class Contract
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
     *
     * @ORM\ManyToOne(targetEntity="Cyclear\GameBundle\Entity\Ploeg")
     */
    private $ploeg;

    /**
     *
     * @ORM\ManyToOne(targetEntity="Cyclear\GameBundle\Entity\Renner", inversedBy="contracts")
     */
    private $renner;

    /**
     * @var datetime $start
     *
     * @ORM\Column(name="start", type="datetime")
     */
    private $start;

    /**
     * @var datetime $start
     *
     * @ORM\Column(name="eind", type="datetime", nullable=true)
     */
    private $eind;

    /**
     *
     * @ORM\ManyToOne(targetEntity="Cyclear\GameBundle\Entity\Seizoen")
     */
    private $seizoen;

    public function getId()
    {
        return $this->id;
    }

    public function getPloeg()
    {
        return $this->ploeg;
    }

    public function setPloeg($ploeg)
    {
        $this->ploeg = $ploeg;
    }

    public function getRenner()
    {
        return $this->renner;
    }

    public function setRenner($renner)
    {
        $this->renner = $renner;
    }

    public function getStart()
    {
        return $this->start;
    }

    public function setStart(\DateTime $start)
    {
        $this->start = $start;
    }

    public function getEind()
    {
        return $this->eind;
    }

    public function setEind($eind)
    {
        $this->eind = $eind;
    }

    public function getSeizoen()
    {
        return $this->seizoen;
    }

    public function setSeizoen($seizoen)
    {
        $this->seizoen = $seizoen;
    }
}
