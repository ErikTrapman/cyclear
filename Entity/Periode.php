<?php

namespace Cyclear\GameBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Cyclear\GameBundle\Entity\Periode
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Periode
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
     * @var datetime $start
     *
     * @ORM\Column(name="start", type="datetime")
     */
    private $start;

    /**
     * @var datetime $eind
     *
     * @ORM\Column(name="eind", type="datetime")
     */
    private $eind;

    /**
     * @var smallint $transfers
     *
     * @ORM\Column(name="transfers", type="smallint")
     */
    private $transfers;


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
     * Set start
     *
     * @param datetime $start
     */
    public function setStart($start)
    {
        $this->start = $start;
    }

    /**
     * Get start
     *
     * @return datetime 
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * Set eind
     *
     * @param datetime $eind
     */
    public function setEind($eind)
    {
        $this->eind = $eind;
    }

    /**
     * Get eind
     *
     * @return datetime 
     */
    public function getEind()
    {
        return $this->eind;
    }

    /**
     * Set transfers
     *
     * @param smallint $transfers
     */
    public function setTransfers($transfers)
    {
        $this->transfers = $transfers;
    }

    /**
     * Get transfers
     *
     * @return smallint 
     */
    public function getTransfers()
    {
        return $this->transfers;
    }
}