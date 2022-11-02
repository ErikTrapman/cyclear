<?php declare(strict_types=1);

namespace App\CQRanking;

use App\Entity\UitslagType;
use App\Entity\Wedstrijd;
use Doctrine\ORM\EntityManagerInterface;

class RaceCategoryMatcher
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    /**
     * @param string $category
     * @return UitslagType
     */
    public function getUitslagTypeAccordingToCategory($category)
    {
        $repo = $this->em->getRepository(UitslagType::class);
        /** @var UitslagType $uitslagType */
        foreach ($repo->findAll() as $uitslagType) {
            $pattern = '/^(' . $this->getPregPattern($uitslagType->getAutomaticResolvingCategories()) . ')$/';
            $match = preg_match($pattern, $category);
            if (0 !== $match) {
                return $uitslagType;
            }
        }
        return null;
    }

    /**
     * Tells if given Wedstrijd needs a reference-stage.
     * True if wedstrijdname contains 'general classification'
     *
     * @param $string
     * @return bool
     */
    public function needsRefStage(Wedstrijd $wedstrijd)
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

    /**
     * @return Wedstrijd
     */
    public function getRefStage(Wedstrijd $wedstrijd)
    {
        return $this->em->getRepository(Wedstrijd::class)->getRefStage($wedstrijd);
    }

    /**
     * @param string $string
     */
    public function getPregPattern($string)
    {
        return implode('|', explode(',', str_replace(' ', '', $string)));
    }
}
