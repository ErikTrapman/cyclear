<?php declare(strict_types=1);

namespace App\Twig;

use App\Entity\Award;
use App\Entity\Ploeg;
use App\Repository\AwardRepository;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AwardExtension extends AbstractExtension
{
    /** @var Award[]|null */
    private ?array $awards = null;

    public function __construct(
        private readonly AwardRepository $awardRepository,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('awards', [$this, 'getAwards']),
            new TwigFunction('trophies', [$this, 'getTrophies']),
        ];
    }

    /**
     * @return Award[]
     */
    public function getAwards(): array
    {
        return $this->awards ??= $this->awardRepository->findAllWithPloeg();
    }

    /**
     * All awards of the user behind this ploeg, over all seasons. Falls back to the ploeg itself when it has no user.
     *
     * @return Award[]
     */
    public function getTrophies(Ploeg $ploeg): array
    {
        // compare ids: standings come from the cache, so their entities are not the managed instances
        $userId = $ploeg->getUser()?->getId();
        return array_values(array_filter(
            $this->getAwards(),
            static fn (Award $award): bool => null !== $userId
                ? $award->getUser()?->getId() === $userId
                : $award->getPloeg()?->getId() === $ploeg->getId(),
        ));
    }
}
