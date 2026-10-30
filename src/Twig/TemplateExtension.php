<?php declare(strict_types=1);

namespace App\Twig;

use App\Entity\Seizoen;
use App\Repository\SeizoenRepository;
use Psr\Clock\ClockInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class TemplateExtension extends AbstractExtension
{
    /** month => the grand tour that colors the title ribbon */
    private const GRAND_TOURS = [5 => 'giro', 7 => 'tour', 9 => 'vuelta'];

    public function __construct(
        private readonly SeizoenRepository $seizoenRepository,
        private readonly ClockInterface $clock,
        private readonly RequestStack $requestStack,
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
        $month = (int)$this->clock->now()->format('n');
        // TEMPORARY: ?maand=5 previews the ribbon of another month
        $month = $this->requestStack->getMainRequest()?->query->getInt('maand') ?: $month;

        return self::GRAND_TOURS[$month] ?? null;
    }
}
