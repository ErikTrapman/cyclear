<?php declare(strict_types=1);

/*
 * This file is part of the Cyclear-game package.
 *
 * (c) Erik Trapman <veggatron@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * App\Entity\Periode
 *
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="App\Repository\PeriodeRepository")
 * @ORM\Table(name="periode")
 */
class Periode
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var datetime
     *
     * @ORM\Column(name="start", type="date")
     */
    private $start;

    /**
     * @var datetime
     *
     * @ORM\Column(name="eind", type="date")
     */
    private $eind;

    /**
     * @var smallint
     *
     * @ORM\Column(name="transfers", type="smallint", nullable=true)
     */
    private $transfers;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Seizoen")
     */
    private $seizoen;

    /**
     * Get id
     *
     * @return int
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

    public function getSeizoen()
    {
        return $this->seizoen;
    }

    public function setSeizoen($seizoen)
    {
        $this->seizoen = $seizoen;
    }

    public function getEnd()
    {
        return $this->getEind();
    }
}
