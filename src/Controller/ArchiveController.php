<?php declare(strict_types=1);

namespace App\Controller;

use App\Entity\AwardType;
use App\Repository\AwardRepository;
use App\Repository\SeizoenRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/archief')]
class ArchiveController extends AbstractController
{
    public function __construct(
        private readonly SeizoenRepository $seizoenRepository,
        private readonly AwardRepository $awardRepository,
    ) {
    }

    #[Route(path: '', name: 'archief_index')]
    public function indexAction(): Response
    {
        $winners = [];
        $olderWinners = [];
        foreach ($this->awardRepository->findAllWithPloeg() as $award) {
            if (AwardType::SeasonWinner !== $award->getType()) {
                continue;
            }
            if (null === $ploeg = $award->getPloeg()) {
                // seasons of the edition before Cyclear 2014 are not in the database
                $olderWinners[] = $award;
            } else {
                $winners[$ploeg->getSeizoen()->getId()] = $award;
            }
        }

        $rows = [];
        foreach (array_reverse($this->seizoenRepository->getArchived()) as $seizoen) {
            $rows[] = ['seizoen' => $seizoen, 'label' => $seizoen->getIdentifier(), 'winner' => $winners[$seizoen->getId()] ?? null];
        }
        foreach (array_reverse($olderWinners) as $award) {
            $rows[] = ['seizoen' => null, 'label' => $award->getSeason(), 'winner' => $award];
        }

        return $this->render('archive/index.html.twig', ['rows' => $rows]);
    }
}
