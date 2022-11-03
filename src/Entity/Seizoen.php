<?php declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SeizoenRepository")
 * @ORM\Table(name="seizoen")
 */
class Seizoen
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column
     */
    private $identifier;

    /**
     * @Gedmo\Slug(fields={"identifier"})
     * @ORM\Column(length=128, unique=true)
     */
    private $slug;

    /**
     * @ORM\Column(type="boolean")
     */
    private $closed = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $current = false;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $start;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $end;

    /**
     * @ORM\Column(type="integer", nullable=true, name="maxPointsPerRider")
     */
    private $maxPointsPerRider;

    public function getId()
    {
        return $this->id;
    }

    public function getIdentifier()
    {
        return $this->identifier;
    }

    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
    }

    public function getClosed()
    {
        return $this->closed;
    }

    public function setClosed($closed)
    {
        $this->closed = $closed;
    }

    public function getSlug()
    {
        return $this->slug;
    }

    public function isCurrent()
    {
        return (bool)$this->current;
    }

    public function setCurrent($current)
    {
        $this->current = $current;
    }

    public function getStart()
    {
        return $this->start;
    }

    public function setStart($start)
    {
        $this->start = $start;
    }

    public function getEnd()
    {
        return $this->end;
    }

    public function setEnd($end)
    {
        $this->end = $end;
    }

    public function getMaxPointsPerRider()
    {
        return $this->maxPointsPerRider;
    }

    public function setMaxPointsPerRider($maxPointsPerRider)
    {
        $this->maxPointsPerRider = $maxPointsPerRider;
    }

    public function __toString()
    {
        return $this->identifier;
    }
}
