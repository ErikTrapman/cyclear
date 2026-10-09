<?php declare(strict_types=1);

namespace App\Entity;

use App\CQRanking\Parser\Strategy\AbstractStrategy;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'uitslag_type')]
class UitslagType
{
    /**
     * @var int
     */
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private $id;

    #[ORM\Column(name: 'naam', type: 'string')]
    private $naam;

    #[ORM\Column(name: 'maxResults', type: 'integer')]
    private $maxResults;

    #[ORM\Column(name: 'isGeneralClassification', type: 'boolean')]
    private $isGeneralClassification;

    /**
     * Class name of the parsing strategy. The strategies are stateless, so an instance is created on read.
     *
     * @var class-string<AbstractStrategy>|null
     */
    #[ORM\Column(name: 'cqParsingStrategy', length: 255)]
    private ?string $cqParsingStrategy = null;

    #[ORM\Column(nullable: true, name: 'automaticResolvingCategories')]
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

    public function getCqParsingStrategy(): ?AbstractStrategy
    {
        return null !== $this->cqParsingStrategy ? new $this->cqParsingStrategy() : null;
    }

    public function setCqParsingStrategy(?AbstractStrategy $cqParsingStrategy): void
    {
        $this->cqParsingStrategy = null !== $cqParsingStrategy ? $cqParsingStrategy::class : null;
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

    public function __toString(): string
    {
        return $this->getNaam();
    }
}
