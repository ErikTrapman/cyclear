<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\Periode;
use App\Entity\Ploeg;
use App\Entity\Renner;
use App\Entity\Seizoen;
use App\Entity\Transfer;
use App\Entity\Uitslag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class UitslagRepository extends ServiceEntityRepository
{
    public const CACHE_TAG = 'UitslagRepository';

    /**
     * Marks cache keys whose entries hold team ids instead of serialized Ploeg entities,
     * so entries written in the old format are never read back.
     */
    private const TEAM_IDS_KEY = '-teamids-';

    public function __construct(
        ManagerRegistry $registry,
        private readonly TagAwareCacheInterface $cache,
        private readonly SeizoenRepository $seizoenRepository,
        private readonly PloegRepository $ploegRepository,
        private readonly RennerRepository $rennerRepository,
        private readonly TransferRepository $transferRepository,
    ) {
        parent::__construct($registry, Uitslag::class);
    }

    public static function puntenSort(array &$values, $fallBackSort = 'afkorting', $sortKey = 'punten'): void
    {
        uasort($values, function ($a, $b) use ($fallBackSort, $sortKey) {
            if ($a instanceof Ploeg && $b instanceof Ploeg) {
                $aPoints = $a->getPunten();
                $bPoints = $b->getPunten();
            } else {
                $aPoints = $a[$sortKey];
                $bPoints = $b[$sortKey];
            }
            if ($aPoints == $bPoints) {
                if ($a instanceof Ploeg && $b instanceof Ploeg) {
                    $accessor = PropertyAccess::createPropertyAccessor();
                    return $accessor->getValue($a, $fallBackSort) < $accessor->getValue($b, $fallBackSort) ? -1 : 1;
                } else {
                    return $a[$fallBackSort] < $b[$fallBackSort] ? -1 : 1;
                }
            }
            return ($aPoints < $bPoints) ? 1 : -1;
        });
    }

    public function getPuntenByPloeg($seizoen = null, $ploeg = null, ?\DateTime $maxDate = null)
    {
        $key = __FUNCTION__ . self::TEAM_IDS_KEY . $seizoen?->getId() . $ploeg?->getId() . $maxDate?->format('YmdHis');
        $item = $this->cache->getItem($key);
        if ($item->isHit()) {
            return $this->withTeams($item->get(), 'punten');
        }
        $seizoen = $this->resolveSeizoen($seizoen);
        $params = ['seizoen' => $seizoen];
        $subQuery = $this->createQueryBuilder('u')
            ->select('ifnull(sum(u.ploegPunten),0)')
            ->innerJoin('u.wedstrijd', 'w')->where('w.seizoen = :seizoen')
            ->andWhere('u.ploeg = p')
            ->setParameter('seizoen', $seizoen);
        if (null !== $maxDate) {
            $subQuery->andWhere('w.datum < :maxdate');
            $maxDate->setTime(0, 0, 0);
            // $subQuery->setParameter('maxdate', $maxDate);
            $params['maxdate'] = $maxDate;
        }

        $qb = $this->ploegRepository->createQueryBuilder('p');
        $qb->addSelect('(' . $subQuery->getDQL() . ') AS punten');
        $qb->where('p.seizoen = :seizoen');
        if (null !== $ploeg) {
            $qb->andWhere('p = :ploeg');
            $params['ploeg'] = $ploeg;
        }
        $qb->orderBy('punten', 'DESC')->addOrderBy('p.afkorting', 'ASC');
        $qb->setParameters(Util::buildParameters($params));
        $value = $qb->getQuery()->getResult();
        $item->set($this->withTeamIds($value, 'punten'));
        $item->tag(self::CACHE_TAG);
        $this->cache->save($item);
        return $value;
    }

    public function getPuntenByPloegForPeriode(Periode $periode, ?Seizoen $seizoen = null): array
    {
        $seizoen = $this->resolveSeizoen($seizoen);
        $start = clone $periode->getStart();
        $start->setTime(0, 0, 0);
        $end = clone $periode->getEind();
        $end->setTime(23, 59, 59);
        $qb = $this->ploegRepository->createQueryBuilder('p');
        $subQ = $this->createQueryBuilder('u')
            ->innerJoin('u.wedstrijd', 'w')
            ->where($qb->expr()->between('w.datum', ':start', ':end'))->andWhere('u.ploeg = p')
            ->select('IFNULL(SUM(u.ploegPunten),0)');
        $qb->select('p')
            ->where('p.seizoen = :seizoen')
            ->addSelect(sprintf('(%s) AS punten', $subQ->getDQL()))
            ->groupBy('p')
            ->orderBy('punten', 'DESC')
            ->addOrderBy('p.afkorting', 'ASC');
        $qb->setParameters(Util::buildParameters(['start' => $start, 'end' => $end, 'seizoen' => $seizoen]));
        // flatten the result
        return array_map(function ($row) {
            return array_merge($row[0], ['punten' => $row['punten']]);
        }, $qb->getQuery()->getArrayResult());
    }

    public function getCountForPosition($seizoen = null, $pos = 1, ?\DateTime $start = null, ?\DateTime $end = null)
    {
        $seizoen = $this->resolveSeizoen($seizoen);
        $key = __FUNCTION__ . self::TEAM_IDS_KEY . $seizoen->getId() . $pos . $start?->format('YmdHis') . $end?->format('YmdHis');
        $item = $this->cache->getItem($key);
        if ($item->isHit()) {
            return $this->withTeams($item->get(), 'freqByPos');
        }
        $parameters = ['seizoen' => $seizoen, 'pos' => $pos];
        $qb2 = $this->createQueryBuilder('u')
            ->select('SUM(IF(u.positie = :pos,1,0))')
            ->join('u.wedstrijd', 'w')
            ->where('u.ploeg = p.id')
            ->andWhere('w.seizoen = :seizoen')
            ->andWhere('u.ploegPunten > 0');
        if ($start && $end) {
            $start = clone $start;
            $start->setTime(0, 0, 0);
            $end = clone $end;
            $end->setTime(23, 59, 59);
            $qb2->andWhere('w.datum >= :start AND w.datum <= :end');
            $parameters['start'] = $start;
            $parameters['end'] = $end;
        }
        $qb = $this->ploegRepository->createQueryBuilder('p');
        $qb
            ->where('p.seizoen = :seizoen')
            ->addSelect(sprintf('IFNULL((%s),0) as freqByPos', $qb2->getDql()))
            ->groupBy('p.id')
            ->orderBy('freqByPos DESC, p.afkorting', 'ASC')
            ->setParameters(Util::buildParameters($parameters));
        $ret = $qb->getQuery()->getResult();
        $item->set($this->withTeamIds($ret, 'freqByPos'));
        $item->tag(self::CACHE_TAG);
        $this->cache->save($item);
        return $ret;
    }

    /**
     * @param Renner $renner
     * @param Seizoen|null $seizoen
     * @param bool $excludeZeros
     * @return array
     */
    public function getPuntenForRenner($renner, $seizoen = null, $excludeZeros = false)
    {
        $seizoen = $this->resolveSeizoen($seizoen);
        $qb = $this->getPuntenForRennerQb();
        $qb->andWhere('w.seizoen = :seizoen');
        if ($excludeZeros) {
            $qb->andWhere('u.rennerPunten > 0');
        }
        $qb->setParameters(Util::buildParameters(['seizoen' => $seizoen, 'renner' => $renner]));
        return $qb->getQuery()->getResult();
    }

    public function getTotalPuntenForRenner(Renner $renner, ?Seizoen $seizoen = null)
    {
        $seizoen = $this->resolveSeizoen($seizoen);
        $qb = $this->getPuntenForRennerQb();
        $qb->andWhere('w.seizoen = :seizoen');
        $qb->setParameters(Util::buildParameters(['seizoen' => $seizoen, 'renner' => $renner]));
        $qb->add('select', 'SUM(u.rennerPunten)');
        return $qb->getQuery()->getSingleScalarResult();
    }

    public function getPuntenForRennerWithPloeg(Renner $renner, Ploeg $ploeg, $seizoen = null)
    {
        $seizoen = $this->resolveSeizoen($seizoen);
        $key = __FUNCTION__ . $renner->getId() . $ploeg->getId() . $seizoen->getId();
        $item = $this->cache->getItem($key);
        if ($item->isHit()) {
            return $item->get();
        }
        $qb = $this->getPuntenForRennerQb();
        $qb->andWhere('w.seizoen = :seizoen')->andWhere('u.ploeg = :ploeg');
        $qb->setParameters(Util::buildParameters(['seizoen' => $seizoen, 'ploeg' => $ploeg, 'renner' => $renner]));
        $qb->add('select', 'SUM(u.ploegPunten)');
        $res = $qb->getQuery()->getSingleScalarResult();
        $item->set($res);
        $item->tag(self::CACHE_TAG);
        $this->cache->save($item);
        return $res;
    }

    private function getPuntenForRennerQb(): QueryBuilder
    {
        $qb = $this->createQueryBuilder('u')
            ->join('u.wedstrijd', 'w')
            ->where('u.renner = :renner')
            ->orderBy('u.id', 'DESC');
        return $qb;
    }

    public function getPuntenByPloegForDraftTransfers(Seizoen $seizoen, ?Ploeg $ploeg = null): array
    {
        $item = $this->cache->getItem('getPuntenByPloegForDraftTransfers' . $seizoen->getId() . $ploeg?->getId());
        if ($item->isHit()) {
            return $item->get();
        }
        if ($ploeg) {
            $subQ = $this->rennerRepository->createQueryBuilder('r');
            $subQ->innerJoin('App\Entity\Transfer', 't', 'WITH',
                't.renner = r AND t.transferType = :draft AND t.seizoen = :seizoen')->andWhere('t.ploegNaar = :p');
            $subQ->select('DISTINCT r.id');
            $subQPoints = $this->createQueryBuilder('u');
            $subQPoints->select('IFNULL(SUM(u.rennerPunten),0)')
                ->groupBy('u.renner')
                ->innerJoin('u.wedstrijd', 'w')
                ->where('w.seizoen = :seizoen')
                ->andWhere($subQ->expr()->in('u.renner', $subQ->getDQL()));
            $subQPoints->setParameter('draft', Transfer::DRAFTTRANSFER)->setParameter('seizoen', $seizoen);
            $retPloeg = $this->ploegRepository->createQueryBuilder('p')->where($subQ->expr()->eq('p', $ploeg->getId()))->getQuery()->getArrayResult();
            $subRes = $subQPoints->setParameter('p', $ploeg)->getQuery()->getScalarResult();
            $retPloeg['punten'] = array_sum(array_map(function ($item) {
                return (int)reset($item);
            }, $subRes));
            return [$retPloeg];
        }
        $res = [];
        $maxPointsPerRider = null !== $seizoen->getMaxPointsPerRider() ? $seizoen->getMaxPointsPerRider() : pow(8, 8);
        $draftRiderIds = $this->transferRepository->getDraftRiderIdsPerTeam($seizoen);
        $seasonPoints = $this->getRennerPuntenPerRider($seizoen, self::riderIdsOf($draftRiderIds));
        foreach ($this->ploegRepository
                     ->createQueryBuilder('p')->where('p.seizoen = :seizoen')
                     ->setParameter('seizoen', $seizoen)->getQuery()->getArrayResult() as $ploeg) {
            // all riders can score the max amounts of maxPointsPerRider.
            $ploeg['punten'] = 0;
            foreach (array_keys($draftRiderIds[$ploeg['id']] ?? []) as $riderId) {
                $ploeg['punten'] += (int)min($maxPointsPerRider, $seasonPoints[$riderId] ?? 0);
            }
            $res[] = $ploeg;
        }
        static::puntenSort($res, 'afkorting');
        $item->set($res);
        $item->tag(self::CACHE_TAG);
        $this->cache->save($item);
        return $item->get();
    }

    public function getPuntenByPloegForUserTransfersWithoutLoss(Seizoen $seizoen, ?\DateTime $start = null, ?\DateTime $end = null)
    {
        $key = 'getPuntenByPloegForUserTransfersWithoutLoss_' . $seizoen->getId() . $start?->format('Ymd') . $end?->format('Ymd');
        $item = $this->cache->getItem($key);
        if ($item->isHit()) {
            return $item->get();
        }
        $params = ['seizoen_id' => $seizoen->getId()];
        $startEndWhere = null;
        if ($start && $end) {
            $startEndWhere = ' AND (w.datum >= :start AND w.datum <= :end)';
            $start = clone $start;
            $start->setTime(0, 0, 0);
            $end = clone $end;
            $end->setTime(0, 0, 0);
            $params['start'] = $start->format('Y-m-d H:i:s');
            $params['end'] = $end->format('Y-m-d H:i:s');
        }
        // TODO DQL'en net als getCountForPosition
        $transfers = 'SELECT DISTINCT t.renner_id FROM transfer t
            WHERE t.transferType != ' . Transfer::DRAFTTRANSFER . ' AND t.ploegNaar_id = p.id AND t.seizoen_id = :seizoen_id
                AND t.renner_id NOT IN
                (SELECT t.renner_id FROM transfer t
                WHERE t.transferType = ' . Transfer::DRAFTTRANSFER . ' AND t.ploegNaar_id = p.id
                AND t.seizoen_id = :seizoen_id)';

        $sql = sprintf('
                SELECT p.id AS id, p.naam AS naam, p.afkorting AS afkorting, 100 AS b,
                (

                (SELECT IFNULL(SUM(u.ploegPunten),0)
                FROM uitslag u
                INNER JOIN wedstrijd w ON u.wedstrijd_id = w.id
                WHERE w.seizoen_id = :seizoen_id %s AND u.ploeg_id = p.id AND u.renner_id IN (%s))

                ) AS punten

                FROM ploeg p WHERE p.seizoen_id = :seizoen_id
                ORDER BY punten DESC, p.afkorting ASC
                ', $startEndWhere, $transfers);
        $res = $this->getEntityManager()->getConnection()->executeQuery($sql, $params)->fetchAllAssociative();
        $item->set($res);
        $item->tag(self::CACHE_TAG);
        $this->cache->save($item);
        return $item->get();
    }

    /**
     * Per team, the points its draft riders scored while not riding for it (or riding for it without team points).
     *
     * @return array<array{id: int, afkorting: string, punten: float|int}> sorted by punten
     */
    public function getLostDraftPuntenByPloeg(?Seizoen $seizoen = null, ?\DateTime $start = null, ?\DateTime $end = null)
    {
        $seizoen = $this->resolveSeizoen($seizoen);
        $key = __FUNCTION__ . self::TEAM_IDS_KEY . $seizoen->getId() . $start?->format('YmdHis') . $end?->format('YmdHis');
        $item = $this->cache->getItem($key);
        if ($item->isHit()) {
            return $item->get();
        }
        $maxPointsPerRider = null !== $seizoen->getMaxPointsPerRider() ? $seizoen->getMaxPointsPerRider() : pow(8, 8);
        $draftRiderIds = $this->transferRepository->getDraftRiderIdsPerTeam($seizoen);
        $allDraftRiderIds = self::riderIdsOf($draftRiderIds);
        $seasonPoints = $this->getRennerPuntenPerRider($seizoen, $allDraftRiderIds);

        // the scoring results of all draft riders, for every team at once
        $resultsPerRider = [];
        if ([] !== $allDraftRiderIds) {
            $qb = $this->createQueryBuilder('u')
                ->select('IDENTITY(u.renner) AS renner, IDENTITY(u.ploeg) AS ploeg, u.rennerPunten, u.ploegPunten')
                ->innerJoin('u.wedstrijd', 'w')
                ->where('w.seizoen = :seizoen')
                ->andWhere('u.renner IN (:renners)')
                ->andWhere('u.rennerPunten > 0')
                ->setParameter('seizoen', $seizoen)
                ->setParameter('renners', $allDraftRiderIds);
            if ($start && $end) {
                $start = clone $start;
                $start->setTime(0, 0, 0);
                $end = clone $end;
                $end->setTime(0, 0, 0);
                $qb->andWhere('w.datum >= :start AND w.datum <= :end')->setParameter('start', $start)->setParameter('end', $end);
            }
            foreach ($qb->getQuery()->getScalarResult() as $result) {
                $resultsPerRider[(int)$result['renner']][] = $result;
            }
        }

        $res = [];
        foreach ($this->ploegRepository
                     ->createQueryBuilder('p')->select('p.id, p.afkorting')->where('p.seizoen = :seizoen')
                     ->setParameter('seizoen', $seizoen)->getQuery()->getArrayResult() as $ploeg) {
            $teamPointsPerRider = [];
            foreach (array_keys($draftRiderIds[$ploeg['id']] ?? []) as $riderId) {
                $draftPunten = $seasonPoints[$riderId] ?? 0;
                if (null !== $seizoen->getMaxPointsPerRider()) {
                    $draftPunten = min($seizoen->getMaxPointsPerRider(), $draftPunten);
                }
                // If a drafted rider has > maxPoints we do not calculate the points as "lost"
                // This rider has a valid reason to be transferred and should not be taken into account.
                // This might not be a waterproof solution because a rider can be transferred early and get maxPoints at another team.
                if ((int)$draftPunten >= $maxPointsPerRider) {
                    continue;
                }
                foreach ($resultsPerRider[$riderId] ?? [] as $result) {
                    // points the rider scored for this team aren't lost
                    if ((int)$result['ploeg'] === $ploeg['id'] && 0.0 != (float)$result['ploegPunten']) {
                        continue;
                    }
                    $teamPointsPerRider[$riderId] = ($teamPointsPerRider[$riderId] ?? 0) + (float)$result['rennerPunten'];
                }
            }
            $ploeg['punten'] = array_sum($teamPointsPerRider);
            $res[] = $ploeg;
        }
        static::puntenSort($res);
        $item->set($res);
        $item->tag(self::CACHE_TAG);
        $this->cache->save($item);
        return $res;
    }

    /**
     * The same standings as getPuntenByPloegForUserTransfersWithoutLoss() over the whole season,
     * so both share its cached result.
     */
    public function getPuntenByPloegForUserTransfers(?Seizoen $seizoen = null): array
    {
        return $this->getPuntenByPloegForUserTransfersWithoutLoss($this->resolveSeizoen($seizoen));
    }

    public function getUitslagenForPloegForNonDraftTransfersQb(Ploeg $ploeg, $seizoen = null, ?\DateTime $start = null, ?\DateTime $end = null)
    {
        $seizoen = $this->resolveSeizoen($seizoen);
        $parameters = ['ploeg' => $ploeg, 'seizoen' => $seizoen];
        $transfers = $this->transferRepository->getTransferredInNonDraftRenners($ploeg, $seizoen);

        $qb = $this->createQueryBuilder('u');
        $qb->where('u.ploeg = :ploeg')
            ->join('u.wedstrijd', 'w')
            ->join('u.renner', 'renner')->addSelect('renner')
            ->andWhere('w.seizoen = :seizoen')
            ->andWhere($qb->expr()->in('u.renner', array_merge(array_unique(array_map(function ($a) {
                return $a->getRenner()->getId();
            }, $transfers)), [0])))
            ->andWhere('u.ploegPunten > 0')
            ->setParameters(Util::buildParameters($parameters))
            ->orderBy('w.datum DESC, u.id', 'DESC');
        return $qb;
    }

    /**
     * @param null $seizoen
     * @param mixed $ploeg
     * @return QueryBuilder
     */
    public function getUitslagenForPloegForLostDraftsQb($ploeg, $seizoen = null, ?\DateTime $start = null, ?\DateTime $end = null)
    {
        $seizoen = $this->resolveSeizoen($seizoen);
        $parameters = ['ploeg' => $ploeg, 'seizoen' => $seizoen];
        $draftrenners = $this->ploegRepository->getDraftRenners($ploeg);
        $qb = $this->createQueryBuilder('u');
        $qb
            // ->where('u.ploeg = :ploeg')
            ->join('u.wedstrijd', 'w')
            ->andWhere('w.seizoen = :seizoen')
            ->andWhere($qb->expr()->in('u.renner', array_merge(array_unique(array_map(function ($r) {
                return $r->getId();
            }, $draftrenners)), [0])))
            ->andWhere('u.rennerPunten > 0')
            // ->andWhere('1=1')
            ->andWhere('(u.ploeg != :ploeg OR u.ploeg IS NULL) OR (u.ploeg = :ploeg AND u.ploegPunten = 0)')
            ->setParameters(Util::buildParameters($parameters))
            ->orderBy('w.datum DESC, u.id', 'DESC');
        if ($start && $end) {
            $startEndWhere = '(w.datum >= :start AND w.datum <= :end)';
            $start = clone $start;
            $start->setTime(0, 0, 0);
            $end = clone $end;
            $end->setTime(0, 0, 0);
            $qb->andWhere($startEndWhere)->setParameter('start', $start)->setParameter('end', $end);
        }
        return $qb;
    }

    public function getUitslagenForPloegQb($ploeg, $seizoen = null): QueryBuilder
    {
        $seizoen = $this->resolveSeizoen($seizoen);
        $parameters = ['ploeg' => $ploeg, 'seizoen' => $seizoen];
        return $this->createQueryBuilder('u')
            ->join('u.wedstrijd', 'w')
            ->where('u.ploeg = :ploeg')
            ->andWhere('w.seizoen = :seizoen')
            ->andWhere('u.ploegPunten > 0')
            ->setParameters(Util::buildParameters($parameters))
            ->orderBy('w.datum DESC, u.id', 'DESC');
    }

    public function getUitslagenForPloegByPositionQb($ploeg, $position, $seizoen = null): QueryBuilder
    {
        $seizoen = $this->resolveSeizoen($seizoen);
        $parameters = ['ploeg' => $ploeg, 'seizoen' => $seizoen, 'position' => $position];
        return $this->createQueryBuilder('u')
            ->join('u.wedstrijd', 'w')
            ->where('u.ploeg = :ploeg')
            ->andWhere('w.seizoen = :seizoen')
            ->andWhere('u.ploegPunten > 0')
            ->andWhere('u.positie = :position')
            ->setParameters(Util::buildParameters($parameters))
            ->orderBy('w.datum DESC, u.id', 'DESC');
    }

    /**
     * The points every team got from the riders it transferred in (not drafted), best first.
     *
     * @return array<array{rider: Renner, team: Ploeg, points: float}>
     */
    public function getBestTransfers(Seizoen $seizoen, ?\DateTime $start = null, ?\DateTime $end = null)
    {
        $key = __FUNCTION__ . self::TEAM_IDS_KEY . $seizoen->getId() . $start?->format('Ymd') . $end?->format('Ymd');
        $item = $this->cache->getItem($key);
        if ($item->isHit()) {
            return $this->withRidersAndTeams($item->get());
        }
        $riderIdsPerTeam = $this->transferRepository->getNonDraftRiderIdsPerTeam($seizoen);
        $rows = [];
        if ([] !== $riderIdsPerTeam) {
            foreach ($this->createQueryBuilder('u')
                         ->select('IDENTITY(u.renner) AS renner, IDENTITY(u.ploeg) AS ploeg, SUM(u.ploegPunten) AS points')
                         ->innerJoin('u.wedstrijd', 'w')
                         ->where('w.seizoen = :seizoen')
                         ->andWhere('u.ploeg IN (:ploegen)')
                         ->andWhere('u.renner IN (:renners)')
                         ->andWhere('u.ploegPunten > 0')
                         ->groupBy('u.renner')->addGroupBy('u.ploeg')
                         ->setParameter('seizoen', $seizoen)
                         ->setParameter('ploegen', array_keys($riderIdsPerTeam))
                         ->setParameter('renners', self::riderIdsOf($riderIdsPerTeam))
                         ->getQuery()->getScalarResult() as $row) {
                if (isset($riderIdsPerTeam[(int)$row['ploeg']][(int)$row['renner']])) {
                    $rows[] = ['rider' => (int)$row['renner'], 'team' => (int)$row['ploeg'], 'points' => (float)$row['points']];
                }
            }
        }
        $res = $this->withRidersAndTeams($rows);
        usort($res, fn (array $a, array $b) => [$b['points'], $a['team']->getAfkorting(), $a['rider']->getNaam()]
            <=> [$a['points'], $b['team']->getAfkorting(), $b['rider']->getNaam()]);
        $item->set(array_map(fn (array $row) => ['rider' => $row['rider']->getId(), 'team' => $row['team']->getId(), 'points' => $row['points']], $res));
        $item->tag(self::CACHE_TAG);
        $this->cache->save($item);
        return $res;
    }

    /**
     * Turns [0 => Ploeg, $valueKey => x] rows into cacheable [id, $valueKey] rows.
     * Caching the entities themselves loads every team's user on serialize, and reads them back
     * as detached copies that Doctrine doesn't know, so later lazy loads of those teams query again.
     */
    private function withTeamIds(array $rows, string $valueKey): array
    {
        return array_map(fn (array $row) => ['id' => $row[0]->getId(), $valueKey => $row[$valueKey]], $rows);
    }

    /**
     * The reverse of withTeamIds(): puts managed Ploeg entities back in the rows, loading the teams
     * that aren't in the identity map yet with a single query.
     */
    private function withTeams(array $rows, string $valueKey): array
    {
        $teams = $this->teamsById(array_column($rows, 'id'));
        $ret = [];
        foreach ($rows as $row) {
            // a team deleted since the entry was cached is left out
            if (isset($teams[$row['id']])) {
                $ret[] = [0 => $teams[$row['id']], $valueKey => $row[$valueKey]];
            }
        }
        return $ret;
    }

    /**
     * Managed teams by id, loading the ones not yet in the identity map with a single query.
     *
     * @return array<int, Ploeg>
     */
    private function teamsById(array $ids): array
    {
        $uow = $this->getEntityManager()->getUnitOfWork();
        $missing = array_filter($ids, function ($id) use ($uow): bool {
            $ploeg = $uow->tryGetById($id, Ploeg::class);
            // an uninitialized proxy would still load on its own when the template reads its name
            return false === $ploeg || $uow->isUninitializedObject($ploeg);
        });
        if ([] !== $missing) {
            $this->ploegRepository->findBy(['id' => array_values(array_unique($missing))]);
        }
        $teams = [];
        foreach ($ids as $id) {
            if (false !== $ploeg = $uow->tryGetById($id, Ploeg::class)) {
                $teams[$id] = $ploeg;
            }
        }
        return $teams;
    }

    /**
     * Turns cached [rider id, team id, points] rows back into rows with managed riders (and their countries) and teams.
     */
    private function withRidersAndTeams(array $rows): array
    {
        if ([] === $rows) {
            return [];
        }
        $riders = [];
        foreach ($this->rennerRepository->createQueryBuilder('r')
                     ->leftJoin('r.country', 'c')->addSelect('c')
                     ->where('r.id IN (:ids)')->setParameter('ids', array_values(array_unique(array_column($rows, 'rider'))))
                     ->getQuery()->getResult() as $renner) {
            $riders[$renner->getId()] = $renner;
        }
        $teams = $this->teamsById(array_column($rows, 'team'));
        $ret = [];
        foreach ($rows as $row) {
            if (isset($riders[$row['rider']], $teams[$row['team']])) {
                $ret[] = ['rider' => $riders[$row['rider']], 'team' => $teams[$row['team']], 'points' => $row['points']];
            }
        }
        return $ret;
    }

    /**
     * Season total of rennerPunten per rider: getTotalPuntenForRenner() for many riders in one query.
     * Riders without results are left out.
     *
     * @return array<int, float>
     */
    private function getRennerPuntenPerRider(Seizoen $seizoen, array $riderIds): array
    {
        if ([] === $riderIds) {
            return [];
        }
        $points = [];
        foreach ($this->createQueryBuilder('u')
                     ->select('IDENTITY(u.renner) AS renner, SUM(u.rennerPunten) AS punten')
                     ->innerJoin('u.wedstrijd', 'w')
                     ->where('w.seizoen = :seizoen')
                     ->andWhere('u.renner IN (:renners)')
                     ->groupBy('u.renner')
                     ->setParameter('seizoen', $seizoen)
                     ->setParameter('renners', $riderIds)
                     ->getQuery()->getScalarResult() as $row) {
            $points[(int)$row['renner']] = (float)$row['punten'];
        }
        return $points;
    }

    /**
     * @param array<int, array<int, true>> $riderIdsPerTeam
     *
     * @return int[] every rider id once
     */
    private static function riderIdsOf(array $riderIdsPerTeam): array
    {
        $ids = [];
        foreach ($riderIdsPerTeam as $riderIds) {
            foreach (array_keys($riderIds) as $id) {
                $ids[$id] = $id;
            }
        }
        return array_values($ids);
    }

    private function resolveSeizoen(?Seizoen $seizoen = null): Seizoen
    {
        if (null === $seizoen) {
            $seizoen = $this->seizoenRepository->getCurrent();
        }
        return $seizoen;
    }
}
