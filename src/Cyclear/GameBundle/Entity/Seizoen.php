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
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass="Cyclear\GameBundle\Entity\SeizoenRepository")
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
     *
     * @ORM\Column(type="boolean")
     */
    private $closed = false;

    /**
     *
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

    public function __toString()
    {
        return $this->identifier;
    }

    /**
     * @return \DateTime
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * @param \DateTime
     */
    public function setStart($start)
    {
        $this->start = $start;
    }

    /**
     * @return \DateTime
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * @param \DateTime
     */
    public function setEnd($end)
    {
        $this->end = $end;
    }


}
