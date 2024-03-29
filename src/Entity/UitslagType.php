<?php declare(strict_types=1);

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
     * @var int
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
     */
    private $maxResults;

    /**
     * @ORM\Column(name="isGeneralClassification", type="boolean")
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

    public function getId(): int
    {
        return $this->id;
    }

    public function setId($id): void
    {
        $this->id = $id;
    }

    public function getNaam()
    {
        return $this->naam;
    }

    public function setNaam($naam): void
    {
        $this->naam = $naam;
    }

    public function getMaxResults()
    {
        return $this->maxResults;
    }

    public function setMaxResults(int $maxResults): void
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

    public function setIsGeneralClassification($isGeneralClassification): void
    {
        $this->isGeneralClassification = $isGeneralClassification;
    }

    public function getCqParsingStrategy()
    {
        return $this->cqParsingStrategy;
    }

    public function setCqParsingStrategy($cqParsingStrategy): void
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
    public function setAutomaticResolvingCategories($automaticResolvingCategories): void
    {
        $this->automaticResolvingCategories = $automaticResolvingCategories;
    }

    public function __toString()
    {
        return $this->getNaam();
    }
}
