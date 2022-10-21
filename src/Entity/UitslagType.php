<?php

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
 * App\Entity\UitslagType
 *
 * @ORM\Table(name="uitslag_type")
 * @ORM\Entity
 */
class UitslagType
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
     * @ORM\Column(name="naam", type="string")
     */
    private $naam;

    /**
     * @ORM\Column(name="maxResults", type="integer")
     *
     */
    private $maxResults;

    /**
     * @ORM\Column(name="isGeneralClassification", type="boolean")
     *
     */
    private $isGeneralClassification;

    /**
     * @ORM\Column(name="cqParsingStrategy", type="object")
     */
    private $cqParsingStrategy;

    /**
     * @ORM\Column(nullable=true, name="automaticResolvingCategories")
     */
    private $automaticResolvingCategories;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getNaam()
    {
        return $this->naam;
    }

    public function setNaam($naam)
    {
        $this->naam = $naam;
    }

    public function getMaxResults()
    {
        return $this->maxResults;
    }

    public function setMaxResults($maxResults)
    {
        $this->maxResults = $maxResults;
    }

    public function getIsGeneralClassification()
    {
        return $this->isGeneralClassification;
    }

    public function isGeneralClassification()
    {
        return $this->getIsGeneralClassification();
    }

    public function setIsGeneralClassification($isGeneralClassification)
    {
        $this->isGeneralClassification = $isGeneralClassification;
    }

    public function getCqParsingStrategy()
    {
        return $this->cqParsingStrategy;
    }

    public function setCqParsingStrategy($cqParsingStrategy)
    {
        $this->cqParsingStrategy = $cqParsingStrategy;
    }

    /**
     * @return mixed
     */
    public function getAutomaticResolvingCategories()
    {
        return $this->automaticResolvingCategories;
    }

    /**
     * @param mixed $automaticResolvingCategories
     */
    public function setAutomaticResolvingCategories($automaticResolvingCategories)
    {
        $this->automaticResolvingCategories = $automaticResolvingCategories;
    }

    public function __toString()
    {
        return $this->getNaam();
    }
}