<?php declare(strict_types=1);

namespace App\Calculator;

use App\Entity\Renner;
use App\Entity\Seizoen;
use App\Entity\Transfer;
use App\Repository\TransferRepository;
use App\Repository\UitslagRepository;

class PointsCalculator
{
    public function __construct(
        private readonly TransferRepository $transferRepository,
        private readonly UitslagRepository $uitslagRepository
    ) {
    }

    public function canGetTeamPoints(Renner $renner, \DateTime $wedstrijdDatum, Seizoen $seizoen, \DateTime $referentieDatum = null): bool
    {
        if (null !== $referentieDatum) {
            $transferFromRefDatum = $this->transferRepository->findLastTransferForDate($renner, $referentieDatum, $seizoen);
            if (!$transferFromRefDatum) {
                return false;
            }
            $transferFromWedstrijd = $this->transferRepository->findLastTransferForDate($renner, $wedstrijdDatum, $seizoen);
            // als de transfer vanaf de wedstrijd-datum niet dezelfde is als vanaf de ref-datum, dan is er sprake van
            //  een of meerdere transfers gedurende deze periode. dan geen punten
            if ($transferFromWedstrijd !== $transferFromRefDatum) {
                return false;
            }
            return $this->validateTransfer($transferFromRefDatum, $referentieDatum);
        } else {
            $lastTransfer = $this->transferRepository->findLastTransferForDate($renner, $wedstrijdDatum, $seizoen);
            if (!$lastTransfer) {
                return false;
            }
        }
        // can the rider still get points, or passed the max of this season?
        $currentSeasonalRiderPoints = $this->uitslagRepository->getTotalPuntenForRenner($renner, $seizoen);
        if (null !== $seizoen->getMaxPointsPerRider() && $currentSeasonalRiderPoints >= $seizoen->getMaxPointsPerRider()) {
            return false;
        }
        return $this->validateTransfer($lastTransfer, $wedstrijdDatum);
    }

    public function calculateRiderTeamPoints(Renner $renner, Seizoen $seizoen, int $givenPoints): int
    {
        // can the rider still get points, or passed the max of this season?
        $currentSeasonalRiderPoints = $this->uitslagRepository->getTotalPuntenForRenner($renner, $seizoen);

        $newSeasonalRiderPoints = $currentSeasonalRiderPoints + $givenPoints;

        if ($newSeasonalRiderPoints <= $seizoen->getMaxPointsPerRider()) {
            return $givenPoints;
        }

        return \intval($givenPoints - ($newSeasonalRiderPoints - $seizoen->getMaxPointsPerRider()));
    }

    private function validateTransfer(Transfer $transfer, \DateTime $validationDatum): bool
    {
        $transferDatum = clone $transfer->getDatum();
        $transferDatum->setTime(0, 0, 0);
        $clonedDatum = clone $validationDatum;
        $clonedDatum->setTime(0, 0, 0);
        if ($transferDatum >= $clonedDatum) {
            return false;
        }
        return true;
    }
}
