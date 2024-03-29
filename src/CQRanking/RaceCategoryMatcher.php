<?php declare(strict_types=1);

namespace App\CQRanking;

use App\Entity\UitslagType;
use App\Entity\Wedstrijd;
use App\Repository\WedstrijdRepository;
use Doctrine\ORM\EntityManagerInterface;

class RaceCategoryMatcher
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly WedstrijdRepository $wedstrijdRepository,
    ) {
    }

    public function getUitslagTypeAccordingToCategory(string $category): ?UitslagType
    {
        $repo = $this->em->getRepository(UitslagType::class);
        foreach ($repo->findAll() as $uitslagType) {
            $pattern = '/^(' . $this->getPregPattern($uitslagType->getAutomaticResolvingCategories()) . ')$/';
            $match = preg_match($pattern, $category);
            if (0 !== $match) {
                return $uitslagType;
            }
        }
        return null;
    }

    public function needsRefStage(Wedstrijd $wedstrijd): bool
    {
        $in = 'generalclassification';
        $string = $wedstrijd->getNaam();
        $string = strtolower($string);
        $string = str_replace(' ', '', $string);
        if (false !== stripos($string, $in)) {
            return true;
        }
        return false;
    }

    public function getRefStage(Wedstrijd $wedstrijd): ?Wedstrijd
    {
        return $this->wedstrijdRepository->getRefStage($wedstrijd);
    }

    public function getPregPattern(string $string): string
    {
        return implode('|', explode(',', str_replace(' ', '', $string)));
    }
}
