<?php declare(strict_types=1);

namespace App\Twig;

use App\Entity\Seizoen;
use App\Repository\SeizoenRepository;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class TemplateExtension extends AbstractExtension
{
    public function __construct(
        private readonly SeizoenRepository $seizoenRepository,
    ) {
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('getCurrentSeason', [$this, 'currentSeason']),
        ];
    }

    public function currentSeason(): ?Seizoen
    {
        return $this->seizoenRepository->findOneBy(['current' => true]);
    }
}
