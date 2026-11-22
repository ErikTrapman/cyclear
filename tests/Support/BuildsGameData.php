<?php declare(strict_types=1);

namespace App\Tests\Support;

use App\Entity\Ploeg;
use App\Entity\Renner;
use App\Entity\Seizoen;
use App\Entity\Transfer;
use App\Entity\Uitslag;
use App\Entity\Wedstrijd;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Riders, transfers and race results for tests; persisted, not flushed.
 * The test provides the entity manager and the season to add them to.
 */
trait BuildsGameData
{
    protected int $riderCount = 0;

    abstract protected function gameDataManager(): EntityManagerInterface;

    abstract protected function gameDataSeason(): Seizoen;

    protected function rider(?string $naam = null): Renner
    {
        $renner = new Renner();
        $renner->setNaam($naam ?? 'Renner ' . ++$this->riderCount);
        $this->gameDataManager()->persist($renner);

        return $renner;
    }

    /**
     * A transfer of $renner to $ploeg; with $out, also the inversion that takes $out away from $ploeg.
     */
    protected function transfer(Renner $renner, Ploeg $ploeg, int $type, ?Renner $out = null, ?\DateTime $datum = null): Transfer
    {
        $transfer = $this->newTransfer($renner, $ploeg, null, $type, $datum);
        if (null !== $out) {
            $inversion = $this->newTransfer($out, null, $ploeg, $type, $datum);
            $transfer->setInversionTransfer($inversion);
            $inversion->setInversionTransfer($transfer);
        }

        return $transfer;
    }

    protected function newTransfer(Renner $renner, ?Ploeg $naar, ?Ploeg $van, int $type, ?\DateTime $datum): Transfer
    {
        $transfer = new Transfer();
        $transfer->setRenner($renner);
        $transfer->setPloegNaar($naar);
        $transfer->setPloegVan($van);
        $transfer->setTransferType($type);
        $transfer->setSeizoen($this->gameDataSeason());
        $transfer->setDatum($datum ?? new \DateTime());
        $this->gameDataManager()->persist($transfer);

        return $transfer;
    }

    /**
     * @param array<array{0: Renner, 1: ?Ploeg, 2?: float, 3?: float}> $results rider, team, rider points (10), team points
     *                                                                     (the rider points when there is a team, else 0)
     */
    protected function race(string $naam, array $results, ?\DateTime $datum = null): Wedstrijd
    {
        $wedstrijd = new Wedstrijd();
        $wedstrijd->setNaam($naam);
        $wedstrijd->setDatum($datum ?? new \DateTime('today'));
        $wedstrijd->setSeizoen($this->gameDataSeason());
        $this->gameDataManager()->persist($wedstrijd);
        foreach (array_values($results) as $positie => $result) {
            [$renner, $ploeg] = $result;
            $rennerPunten = $result[2] ?? 10;
            $uitslag = new Uitslag();
            $uitslag->setWedstrijd($wedstrijd);
            $uitslag->setRenner($renner);
            $uitslag->setPloeg($ploeg);
            $uitslag->setPositie($positie + 1);
            $uitslag->setRennerPunten($rennerPunten);
            $uitslag->setPloegPunten($result[3] ?? (null === $ploeg ? 0 : $rennerPunten));
            $wedstrijd->addUitslag($uitslag);
            $this->gameDataManager()->persist($uitslag);
        }

        return $wedstrijd;
    }
}
