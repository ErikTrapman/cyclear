<?php declare(strict_types=1);

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
     * @ORM\Column(name="start", type="date")
     */
    private $start;

    /**
     * @ORM\Column(name="eind", type="date")
     */
    private $eind;

    /**
     * @ORM\Column(name="transfers", type="smallint", nullable=true)
     */
    private $transfers;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Seizoen")
     */
    private $seizoen;

    public function getId()
    {
        return $this->id;
    }

    public function setStart($start)
    {
        $this->start = $start;
    }

    public function getStart()
    {
        return $this->start;
    }

    public function setEind($eind)
    {
        $this->eind = $eind;
    }

    public function getEind()
    {
        return $this->eind;
    }

    public function setTransfers($transfers)
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

    public function setSeizoen($seizoen)
    {
        $this->seizoen = $seizoen;
    }

    public function getEnd()
    {
        return $this->getEind();
    }
}
