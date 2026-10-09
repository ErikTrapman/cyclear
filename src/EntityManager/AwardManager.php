<?php declare(strict_types=1);

namespace App\EntityManager;

use App\Entity\Award;
use App\Entity\AwardType;
use App\Entity\Seizoen;
use App\Repository\AwardRepository;
use App\Repository\UitslagRepository;
use Doctrine\ORM\EntityManagerInterface;

class AwardManager
{
    public function __construct(
        private readonly AwardRepository $awardRepository,
        private readonly UitslagRepository $uitslagRepository,
        private readonly EntityManagerInterface $em,
    ) {
    }

    /**
     * Awards the leader of the final standings. Does nothing when the season already has a winner or no standings.
     */
    public function awardSeasonWinner(Seizoen $seizoen): ?Award
    {
        if ($this->awardRepository->hasAwardInSeizoen($seizoen, AwardType::SeasonWinner)) {
            return null;
        }
        $stand = $this->uitslagRepository->getPuntenByPloeg($seizoen);
        if ([] === $stand || 0 == $stand[0]['punten']) {
            return null;
        }
        $award = new Award($stand[0][0], AwardType::SeasonWinner);
        $this->em->persist($award);
        return $award;
    }
}
