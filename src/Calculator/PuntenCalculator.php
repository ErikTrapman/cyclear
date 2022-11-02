<?php declare(strict_types=1);

namespace App\Calculator;

use App\Entity\Renner;
use App\Entity\Seizoen;
use App\Entity\Transfer;
use App\Entity\Uitslag;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Calculate points for a team.
 */
class PuntenCalculator
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function canGetTeamPoints(Renner $renner, \DateTime $wedstrijdDatum, Seizoen $seizoen, \DateTime $referentieDatum = null): bool
    {
        $transferRepo = $this->em->getRepository(Transfer::class);
        if (null !== $referentieDatum) {
            $transferFromRefDatum = $transferRepo->findLastTransferForDate($renner, $referentieDatum, $seizoen);
            if (!$transferFromRefDatum) {
                return false;
            }
            $transferFromWedstrijd = $transferRepo->findLastTransferForDate($renner, $wedstrijdDatum, $seizoen);
            // als de transfer vanaf de wedstrijd-datum niet dezelfde is als vanaf de ref-datum, dan is er sprake van
            //  een of meerdere transfers gedurende deze periode. dan geen punten
            if ($transferFromWedstrijd !== $transferFromRefDatum) {
                return false;
            }
            return $this->validateTransfer($transferFromRefDatum, $referentieDatum);
        } else {
            $lastTransfer = $transferRepo->findLastTransferForDate($renner, $wedstrijdDatum, $seizoen);
            if (!$lastTransfer) {
                return false;
            }
        }
        // can the rider still get points, or passed the max of this season?
        $currentSeasonalRiderPoints = $this->em->getRepository(Uitslag::class)->getTotalPuntenForRenner($renner, $seizoen);
        if (null !== $seizoen->getMaxPointsPerRider() && $currentSeasonalRiderPoints >= $seizoen->getMaxPointsPerRider()) {
            return false;
        }
        return $this->validateTransfer($lastTransfer, $wedstrijdDatum);
    }

    /**
     * @param Transfer $transfer
     * @param \DateTime $validationDatum
     * @return bool
     */
    private function validateTransfer($transfer, $validationDatum)
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
