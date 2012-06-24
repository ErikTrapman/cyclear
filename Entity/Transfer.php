<?php

namespace Cyclear\GameBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Cyclear\GameBundle\Entity\Transfer
 *
 * @ORM\Table(name="Transfer")
 * @ORM\Entity(repositoryClass="Cyclear\GameBundle\Entity\TransferRepository")
 */
class Transfer
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
     * @var Renner $renner
     *
     * @ORM\JoinColumn(name="renner_id")
     * @ORM\ManyToOne(targetEntity="Cyclear\GameBundle\Entity\Renner")
     */
    private $renner;

    /**
     * @var Ploeg $ploegVan
     *
     * @ORM\JoinColumn(name="ploegvan_id", nullable=true)
     * @ORM\ManyToOne(targetEntity="Cyclear\GameBundle\Entity\Ploeg")
     */
    private $ploegVan;

    /**
     * @var Ploeg $ploegNaar
     *
     * @ORM\JoinColumn(name="ploegnaar_id", nullable=true)
     * @ORM\ManyToOne(targetEntity="Cyclear\GameBundle\Entity\Ploeg")
     */
    private $ploegNaar;

    /**
     * @var date $datum
     *
     * @ORM\Column(name="datum", type="datetime")
     */
    private $datum;


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
     * @return object 
     */
    public function getRenner()
    {
        return $this->renner;
    }

    /**
     * Set ploegVan
     *
     * @param object $ploegVan
     */
    public function setPloegVan($ploegVan)
    {
        $this->ploegVan = $ploegVan;
    }

    /**
     * Get ploegVan
     *
     * @return object 
     */
    public function getPloegVan()
    {
        return $this->ploegVan;
    }

    /**
     * Set ploegNaar
     *
     * @param object $ploegNaar
     */
    public function setPloegNaar($ploegNaar)
    {
        $this->ploegNaar = $ploegNaar;
    }

    /**
     * Get ploegNaar
     *
     * @return object 
     */
    public function getPloegNaar()
    {
        return $this->ploegNaar;
    }

    /**
     * Set datum
     *
     * @param date $datum
     */
    public function setDatum($datum)
    {
        $this->datum = $datum;
    }

    /**
     * Get datum
     *
     * @return date 
     */
    public function getDatum()
    {
        return $this->datum;
    }
}