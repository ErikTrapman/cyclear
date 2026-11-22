<?php declare(strict_types=1);

namespace App\Twig;

use App\Entity\Ploeg;
use App\Entity\Renner;
use App\Repository\TransferRepository;
use Symfony\Contracts\Service\ResetInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * isDraftTransfer(renner, ploeg) for result lists: loads a season's draft riders once per request
 * instead of running a query for every result row.
 */
class DraftTransferExtension extends AbstractExtension implements ResetInterface
{
    /** @var array<int, array<int, array<int, true>>> draft rider ids per team id, per season id */
    private array $draftRiderIds = [];

    public function __construct(
        private readonly TransferRepository $transferRepository,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('isDraftTransfer', [$this, 'isDraftTransfer']),
        ];
    }

    public function isDraftTransfer(Renner $renner, Ploeg $ploeg): bool
    {
        $seizoen = $ploeg->getSeizoen();
        if (null === $seizoen) {
            return (bool)$this->transferRepository->hasDraftTransfer($renner, $ploeg);
        }
        $perTeam = $this->draftRiderIds[$seizoen->getId()] ??= $this->transferRepository->getDraftRiderIdsPerTeam($seizoen);

        return isset($perTeam[$ploeg->getId()][$renner->getId()]);
    }

    public function reset(): void
    {
        $this->draftRiderIds = [];
    }
}
