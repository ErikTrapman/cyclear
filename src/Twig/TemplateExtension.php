<?php declare(strict_types=1);

namespace App\Twig;

use App\Entity\Seizoen;
use App\Repository\SeizoenRepository;
use Psr\Clock\ClockInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class TemplateExtension extends AbstractExtension
{
    /** month => the grand tour that colors the title ribbon */
    private const GRAND_TOURS = [5 => 'giro', 7 => 'tour', 9 => 'vuelta'];

    public function __construct(
        private readonly SeizoenRepository $seizoenRepository,
        private readonly ClockInterface $clock,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('getCurrentSeason', [$this, 'currentSeason']),
            new TwigFunction('grandTour', [$this, 'grandTour']),
        ];
    }

    public function currentSeason(): ?Seizoen
    {
        return $this->seizoenRepository->findOneBy(['current' => true]);
    }

    public function grandTour(): ?string
    {
        return self::GRAND_TOURS[(int)$this->clock->now()->format('n')] ?? null;
    }
}
