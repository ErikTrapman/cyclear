<?php declare(strict_types=1);

namespace App\Tests\Repository;

use App\Entity\Ploeg;
use App\Entity\Seizoen;
use App\Entity\Transfer;
use App\Repository\UitslagRepository;
use App\Tests\Fixtures\LoadPloegData;
use App\Tests\Fixtures\LoadSeizoenData;
use App\Tests\Support\BuildsGameData;
use Doctrine\ORM\EntityManagerInterface;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Cached standings must come back with teams Doctrine manages, not detached copies;
 * lost draft points follow the game rules.
 */
class UitslagRepositoryTest extends KernelTestCase
{
    use BuildsGameData;

    private EntityManagerInterface $em;

    private UitslagRepository $repository;

    protected function setUp(): void
    {
        self::bootKernel();
        self::getContainer()->get(DatabaseToolCollection::class)->get()->loadFixtures([
            LoadSeizoenData::class,
            LoadPloegData::class,
        ]);
        $this->em = self::getContainer()->get(EntityManagerInterface::class);
        $this->repository = self::getContainer()->get(UitslagRepository::class);
        $seizoen = $this->seizoen();
        foreach ($this->em->getRepository(Ploeg::class)->findAll() as $ploeg) {
            $ploeg->setSeizoen($seizoen);
        }
        // the flush also invalidates the cached standings
        $this->em->flush();
        $this->em->clear();
    }

    public function testCachedPointsStandingsHoldManagedTeams(): void
    {
        $fresh = $this->teamsOf($this->repository->getPuntenByPloeg($this->seizoen()), 'punten');
        $this->em->clear();

        $cached = $this->repository->getPuntenByPloeg($this->seizoen());

        $this->assertSame($fresh, $this->teamsOf($cached, 'punten'));
        $this->assertAllManaged($cached);
    }

    public function testCachedPositionCountsHoldManagedTeams(): void
    {
        $fresh = $this->teamsOf($this->repository->getCountForPosition($this->seizoen(), 1), 'freqByPos');
        $this->em->clear();

        $cached = $this->repository->getCountForPosition($this->seizoen(), 1);

        $this->assertSame($fresh, $this->teamsOf($cached, 'freqByPos'));
        $this->assertAllManaged($cached);
    }

    public function testLostDraftPointsAreWhatDraftRidersScoredWithoutTheirTeam(): void
    {
        [$pl1, $pl2] = [$this->team('pl1'), $this->team('pl2')];
        [$elsewhere, $withoutTeamPoints, $forTheTeam] = [$this->rider(), $this->rider(), $this->rider()];
        foreach ([$elsewhere, $withoutTeamPoints, $forTheTeam] as $renner) {
            $this->transfer($renner, $pl1, Transfer::DRAFTTRANSFER);
        }
        $this->race('Race', [[$elsewhere, $pl2, 5], [$withoutTeamPoints, $pl1, 7, 0], [$forTheTeam, $pl1, 9]]);
        $this->em->flush();

        $this->assertSame([$pl1->getId() => 12.0, $pl2->getId() => 0], $this->lostPerTeam());
    }

    public function testDraftRidersAtTheSeasonMaximumLoseNothing(): void
    {
        $this->seizoen()->setMaxPointsPerRider(10);
        $pl1 = $this->team('pl1');
        [$maxedOut, $below] = [$this->rider(), $this->rider()];
        $this->transfer($maxedOut, $pl1, Transfer::DRAFTTRANSFER);
        $this->transfer($below, $pl1, Transfer::DRAFTTRANSFER);
        $this->race('Race', [[$maxedOut, null, 12], [$below, null, 4]]);
        $this->em->flush();

        $this->assertSame(4.0, $this->lostPerTeam()[$pl1->getId()]);
        // and the draft standings count the maxed-out rider for the maximum only
        $draft = array_column($this->repository->getPuntenByPloegForDraftTransfers($this->seizoen()), 'punten', 'afkorting');
        $this->assertSame(['pl1' => 14, 'pl2' => 0], $draft);
    }

    public function testLostDraftPointsOfAPeriodOnlyCountItsRaces(): void
    {
        $pl1 = $this->team('pl1');
        $renner = $this->rider();
        $this->transfer($renner, $pl1, Transfer::DRAFTTRANSFER);
        $this->race('Before', [[$renner, null, 5]], new \DateTime('2026-01-10'));
        $this->race('During', [[$renner, null, 3]], new \DateTime('2026-03-10'));
        $this->race('Last day', [[$renner, null, 2]], new \DateTime('2026-03-31'));
        $this->em->flush();

        $lost = $this->lostPerTeam(new \DateTime('2026-03-01'), new \DateTime('2026-03-31'));

        $this->assertSame(5.0, $lost[$pl1->getId()]);
    }

    /**
     * @return array<int, float|int> lost draft points per team id
     */
    private function lostPerTeam(?\DateTime $start = null, ?\DateTime $end = null): array
    {
        $lost = array_column($this->repository->getLostDraftPuntenByPloeg($this->seizoen(), $start, $end), 'punten', 'id');
        ksort($lost);

        return $lost;
    }

    private function team(string $afkorting): Ploeg
    {
        return $this->em->getRepository(Ploeg::class)->findOneBy(['afkorting' => $afkorting]);
    }

    private function seizoen(): Seizoen
    {
        return $this->em->getRepository(Seizoen::class)->findOneBy([]);
    }

    protected function gameDataManager(): EntityManagerInterface
    {
        return $this->em;
    }

    protected function gameDataSeason(): Seizoen
    {
        return $this->seizoen();
    }

    /**
     * @return array<array{string, string}> abbreviation and value per row, in order
     */
    private function teamsOf(array $rows, string $valueKey): array
    {
        return array_map(fn (array $row) => [$row[0]->getAfkorting(), (string)$row[$valueKey]], $rows);
    }

    private function assertAllManaged(array $rows): void
    {
        $this->assertCount(2, $rows);
        foreach ($rows as $row) {
            $this->assertInstanceOf(Ploeg::class, $row[0]);
            $this->assertTrue($this->em->contains($row[0]), $row[0]->getAfkorting() . ' is not managed');
        }
    }
}
