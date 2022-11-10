<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\Periode;
use App\Entity\Ploeg;
use App\Entity\Renner;
use App\Entity\Seizoen;
use App\Entity\Transfer;
use App\Entity\Uitslag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class UitslagRepository extends ServiceEntityRepository
{
    public const CACHE_TAG = 'UitslagRepository';

    public function __construct(ManagerRegistry $registry, private TagAwareCacheInterface $cache)
    {
        parent::__construct($registry, Uitslag::class);
    }


    /**
     * @param $values
     * @param string $fallBackSort
     * @param mixed $sortKey
     * @param ((array|mixed)[]|mixed)[] $values
     *
     * @psalm-param array<array{total: mixed, hits?: mixed, renners?: array}|mixed> $values
     */
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

    public function getPuntenByPloeg($seizoen = null, $ploeg = null, \DateTime $maxDate = null)
    {
        if (null === $seizoen) {
            $seizoen = $this->_em->getRepository(Seizoen::class)->getCurrent();
        }
        $params = ['seizoen' => $seizoen];
        $subQuery = $this->createQueryBuilder('u')
            ->select('ifnull(sum(u.ploegPunten),0)')
            ->innerJoin('u.wedstrijd', 'w')->where('w.seizoen = :seizoen')
            ->andWhere('u.ploeg = p')->setParameters(['seizoen' => $seizoen]);
        if (null !== $maxDate) {
            $subQuery->andWhere('w.datum < :maxdate');
            $maxDate->setTime(0, 0, 0);
            //$subQuery->setParameter('maxdate', $maxDate);
            $params['maxdate'] = $maxDate;
        }

        $qb = $this->_em->getRepository(Ploeg::class)->createQueryBuilder('p');
        $qb->addSelect('(' . $subQuery->getDQL() . ') AS punten');
        $qb->where('p.seizoen = :seizoen');
        if (null !== $ploeg) {
            $qb->andWhere('p = :ploeg');
            $params['ploeg'] = $ploeg;
        }
        $qb->orderBy('punten', 'DESC, p.afkorting ASC');
        $qb->setParameters($params);
        return $qb->getQuery()->getResult();
    }

    /**
     * @return array[]
     *
     * @psalm-return array<array>
     */
    public function getPuntenByPloegForPeriode(Periode $periode, $seizoen = null): array
    {
        if (null === $seizoen) {
            $seizoen = $this->_em->getRepository(Seizoen::class)->getCurrent();
        }
        $start = clone $periode->getStart();
        $start->setTime(0, 0, 0);
        $end = clone $periode->getEind();
        $end->setTime(23, 59, 59);
        $qb = $this->_em->getRepository(Ploeg::class)->createQueryBuilder('p');
        $subQ = $this->_em->getRepository(Uitslag::class)->createQueryBuilder('u')
            ->innerJoin('u.wedstrijd', 'w')
            ->where($qb->expr()->between('w.datum', ':start', ':end'))->andWhere('u.ploeg = p')
            ->select('IFNULL(SUM(u.ploegPunten),0)');
        $qb->select('p')
            ->where('p.seizoen = :seizoen')
            ->addSelect(sprintf('(%s) AS punten', $subQ->getDQL()))
            ->groupBy('p')
            ->orderBy('punten', 'DESC')
            ->addOrderBy('p.afkorting', 'ASC');
        $qb->setParameters(['start' => $start, 'end' => $end, 'seizoen' => $seizoen]);
        // flatten the result
        return array_map(function ($row) {
            return array_merge($row[0], ['punten' => $row['punten']]);
        }, $qb->getQuery()->getArrayResult());
    }

    public function getCountForPosition($seizoen = null, $pos = 1, \DateTime $start = null, \DateTime $end = null)
    {
        if (null === $seizoen) {
            $seizoen = $this->_em->getRepository(Seizoen::class)->getCurrent();
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
        $qb = $this->_em->getRepository(Ploeg::class)->createQueryBuilder('p');
        $qb
            ->where('p.seizoen = :seizoen')
            ->addSelect(sprintf('IFNULL((%s),0) as freqByPos', $qb2->getDql()))
            ->groupBy('p.id')
            ->orderBy('freqByPos DESC, p.afkorting', 'ASC')
            ->setParameters($parameters);
        return $qb->getQuery()->getResult();
    }

    /**
     * @param Renner $renner
     * @param Seizoen|null $seizoen
     * @param bool $excludeZeros
     * @return array
     */
    public function getPuntenForRenner($renner, $seizoen = null, $excludeZeros = false)
    {
        if (null === $seizoen) {
            $seizoen = $this->_em->getRepository(Seizoen::class)->getCurrent();
        }
        $qb = $this->getPuntenForRennerQb();
        $qb->andWhere('w.seizoen = :seizoen');
        if ($excludeZeros) {
            $qb->andWhere('u.rennerPunten > 0');
        }
        $qb->setParameters(['seizoen' => $seizoen, 'renner' => $renner]);
        return $qb->getQuery()->getResult();
    }

    /**
     * @param Renner $renner
     * @param null $seizoen
     * @return int
     */
    public function getTotalPuntenForRenner($renner, $seizoen = null)
    {
        if (null === $seizoen) {
            $seizoen = $this->_em->getRepository(Seizoen::class)->getCurrent();
        }
        $qb = $this->getPuntenForRennerQb();
        $qb->andWhere('w.seizoen = :seizoen');
        $qb->setParameters(['seizoen' => $seizoen, 'renner' => $renner]);
        $qb->add('select', 'SUM(u.rennerPunten)');
        return $qb->getQuery()->getSingleScalarResult();
    }

    public function getPuntenForRennerWithPloeg($renner, $ploeg, $seizoen = null)
    {
        if (null === $seizoen) {
            $seizoen = $this->_em->getRepository(Seizoen::class)->getCurrent();
        }
        $qb = $this->getPuntenForRennerQb();
        $qb->andWhere('w.seizoen = :seizoen')->andWhere('u.ploeg = :ploeg');
        $qb->setParameters(['seizoen' => $seizoen, 'ploeg' => $ploeg, 'renner' => $renner]);
        $qb->add('select', 'SUM(u.ploegPunten)');
        return $qb->getQuery()->getSingleScalarResult();
    }

    private function getPuntenForRennerQb(): \Doctrine\ORM\QueryBuilder
    {
        $qb = $this->createQueryBuilder('u')
            ->join('u.wedstrijd', 'w')
            ->where('u.renner = :renner')
            ->orderBy('u.id', 'DESC');
        return $qb;
    }

    /**
     * @return array[]
     *
     * @psalm-return list<array{0: mixed, punten: mixed}>
     */
    public function getPuntenWithRenners($seizoen = null, $limit = 20): array
    {
        if (null === $seizoen) {
            $seizoen = $this->_em->getRepository(Seizoen::class)->getCurrent();
        }
        $qb = $this->createQueryBuilder('u')
            ->join('u.wedstrijd', 'w')
            ->where('w.seizoen =:seizoen')
            ->leftJoin('u.renner', 'r')
            ->groupBy('u.renner')->add('select', 'IFNULL(SUM(u.rennerPunten),0) AS punten', true)
            ->setMaxResults($limit)
            ->setParameters(['seizoen' => $seizoen])
            ->orderBy('punten DESC, r.naam', 'ASC');
        $ret = [];
        foreach ($qb->getQuery()->getResult() as $result) {
            $ret[] = [0 => $result[0]->getRenner(), 'punten' => $result['punten']];
        }
        return $ret;
    }

    /**
     * @return array[]
     *
     * @psalm-return list<array{0: mixed, punten: mixed}>
     */
    public function getPuntenWithRennersNoPloeg($seizoen = null, $limit = 20): array
    {
        if (null === $seizoen) {
            $seizoen = $this->_em->getRepository(Seizoen::class)->getCurrent();
        }
        $rennersWithPloeg = [];
        foreach ($this->_em->getRepository(Renner::class)->getRennersWithPloeg() as $renner) {
            $rennersWithPloeg[] = $renner->getId();
        }
        $qb = $this->createQueryBuilder('u')
            ->join('u.wedstrijd', 'w')
            ->where('w.seizoen =:seizoen')
            ->leftJoin('u.renner', 'r')
            ->groupBy('u.renner')->add('select', 'IFNULL(SUM(u.rennerPunten),0) AS punten', true)
            ->setMaxResults($limit)
            ->setParameters(['seizoen' => $seizoen])
            ->orderBy('punten DESC, r.naam', 'ASC');
        if (!empty($rennersWithPloeg)) {
            $qb->andWhere($qb->expr()->notIn('u.renner', $rennersWithPloeg));
        }
        $ret = [];
        foreach ($qb->getQuery()->getResult() as $result) {
            $ret[] = [0 => $result[0]->getRenner(), 'punten' => $result['punten']];
        }
        return $ret;
    }

    /**
     * @param null $seizoen
     * @return array
     */
    public function getPuntenByPloegForDraftTransfers(Seizoen $seizoen, Ploeg $ploeg = null)
    {
        $item = $this->cache->getItem('getPuntenByPloegForDraftTransfers' . $seizoen?->getId() . $ploeg?->getId());
        $item->tag(self::CACHE_TAG);
        if (!$item->isHit()) {
            $subQ = $this->_em->getRepository(Renner::class)->createQueryBuilder('r');
            $subQ->innerJoin('App\Entity\Transfer', 't', 'WITH',
                't.renner = r AND t.transferType = :draft AND t.seizoen = :seizoen')->andWhere('t.ploegNaar = :p');
            $subQ->select('DISTINCT r.id');
            $subQPoints = $this->_em->getRepository(Uitslag::class)->createQueryBuilder('u');
            $subQPoints->select('IFNULL(SUM(u.rennerPunten),0)')
                ->groupBy('u.renner')
                ->innerJoin('u.wedstrijd', 'w')
                ->where('w.seizoen = :seizoen')
                ->andWhere($subQ->expr()->in('u.renner', $subQ->getDQL()));
            $subQPoints->setParameter('draft', Transfer::DRAFTTRANSFER)->setParameter('seizoen', $seizoen);
            if ($ploeg) {
                $retPloeg = $this->_em->getRepository(Ploeg::class)
                    ->createQueryBuilder('p')->where($subQ->expr()->eq('p', $ploeg->getId()))->getQuery()->getArrayResult();
                $subRes = $subQPoints->setParameter('p', $ploeg)->getQuery()->getScalarResult();
                $retPloeg['punten'] = array_sum(array_map(function ($item) {
                    return (int)reset($item);
                }, $subRes));
                return [$retPloeg];
            }
            $res = [];
            $maxPointsPerRider = null !== $seizoen->getMaxPointsPerRider() ? $seizoen->getMaxPointsPerRider() : pow(8, 8);
            foreach ($this->_em->getRepository(Ploeg::class)
                         ->createQueryBuilder('p')->where('p.seizoen = :seizoen')
                         ->setParameter('seizoen', $seizoen)->getQuery()->getArrayResult() as $ploeg) {
                $subQPoints->setParameter('p', $ploeg['id']);
                $subRes = $subQPoints->getQuery()->getArrayResult();
                // results are grouped by rider. all riders can score the max amounts of maxPointsPerRider.
                $ploeg['punten'] = array_sum(array_map(function ($item) use ($maxPointsPerRider) {
                    return (int)min($maxPointsPerRider, reset($item));
                }, $subRes));
                $res[] = $ploeg;
            }
            static::puntenSort($res, 'afkorting');
            $item->set($res);
            $this->cache->save($item);
        }
        return $item->get();
    }

    public function getPuntenByPloegForUserTransfersWithoutLoss(Seizoen $seizoen, \DateTime $start = null, \DateTime $end = null)
    {
        $key = 'getPuntenByPloegForUserTransfersWithoutLoss_' . $seizoen->getId() . $start?->format('Ymd') . $end?->format('Ymd');
        $item = $this->cache->getItem($key);
        $item->tag(self::CACHE_TAG);
        if (!$item->isHit()) {
            $params = [':seizoen_id' => $seizoen->getId(), 'transfertype_draft' => Transfer::DRAFTTRANSFER];
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
            $conn = $this->getEntityManager()->getConnection();
            $stmt = $conn->prepare($sql);
            $res = $stmt->executeQuery($params)->fetchAllAssociative();
            $item->set($res);
            $this->cache->save($item);
        }
        return $item->get();
    }

    /**
     * @param Seizoen|null $seizoen
     * @return array
     */
    public function getLostDraftPuntenByPloeg($seizoen = null, \DateTime $start = null, \DateTime $end = null)
    {
        if (null === $seizoen) {
            $seizoen = $this->_em->getRepository(Seizoen::class)->getCurrent();
        }
        $res = [];
        $maxPointsPerRider = null !== $seizoen->getMaxPointsPerRider() ? $seizoen->getMaxPointsPerRider() : pow(8, 8);
        foreach ($this->_em->getRepository(Ploeg::class)
                     ->createQueryBuilder('p')->where('p.seizoen = :seizoen')
                     ->setParameter('seizoen', $seizoen)->getQuery()->getResult() as $ploeg) {
            $teamResults = $this->getUitslagenForPloegForLostDraftsQb($ploeg, $seizoen, $start, $end);
            $teamPointsPerRider = [];
            /** @var Uitslag $teamResult */
            foreach ($teamResults->getQuery()->getResult() as $teamResult) {
                $riderId = $teamResult->getRenner()->getId();
                if (!array_key_exists($riderId, $teamPointsPerRider)) {
                    $teamPointsPerRider[$riderId] = 0;
                }
                $teamPointsPerRider[$riderId] += $teamResult->getRennerPunten();
            }
            // make sure the lost draftpoints are never more than the max points a rider can get.
            $ploeg->setPunten(array_sum(array_map(function ($item) use ($maxPointsPerRider) {
                return (int)min($maxPointsPerRider, $item);
            }, $teamPointsPerRider)));
            $res[] = $ploeg;
        }
        static::puntenSort($res);
        return $res;
    }

    /**
     * @return array[]
     *
     * @psalm-return list<array<string, mixed>>
     */
    public function getPuntenByPloegForUserTransfers($seizoen = null): array
    {
        if (null === $seizoen) {
            $seizoen = $this->_em->getRepository(Seizoen::class)->getCurrent();
        }
        $this->_em->getRepository(Transfer::class)->generateTempTableWithDraftRiders($seizoen);

        // TODO DQL'en net als getCountForPosition
        $transfers = 'SELECT DISTINCT t.renner_id FROM transfer t
            WHERE t.transferType != ' . Transfer::DRAFTTRANSFER . ' AND t.ploegNaar_id = p.id AND t.seizoen_id = :seizoen_id
                AND t.renner_id NOT IN ( SELECT t.renner_id FROM transfer t WHERE t.transferType = ' . Transfer::DRAFTTRANSFER . ' AND t.ploegNaar_id = p.id AND t.seizoen_id = :seizoen_id )';
        $sql = sprintf('SELECT p.id AS id, p.naam AS naam, p.afkorting AS afkorting, 400 as d,
                ((SELECT IFNULL(SUM(u.ploegPunten),0)
                FROM uitslag u
                INNER JOIN wedstrijd w ON u.wedstrijd_id = w.id
                WHERE w.seizoen_id = :seizoen_id AND u.ploeg_id = p.id AND u.renner_id IN (%s))

                -

                (SELECT IFNULL(SUM(u.rennerPunten),0)
                FROM uitslag u
                INNER JOIN wedstrijd w ON u.wedstrijd_id = w.id AND w.seizoen_id = :seizoen_id
                INNER JOIN draftriders dr ON u.renner_id = dr.renner_id
                WHERE dr.ploeg_id = p.id AND (u.ploeg_id IS NULL OR u.ploeg_id <> p.id OR u.ploeg_id = p.id AND u.ploegPunten = 0))

                ) AS punten

                FROM ploeg p WHERE p.seizoen_id = :seizoen_id
                ORDER BY punten DESC, p.afkorting ASC
                ', $transfers);
        $conn = $this->getEntityManager()->getConnection();
        $stmt = $conn->prepare($sql);
        return $stmt->executeQuery([':seizoen_id' => $seizoen->getId(), 'transfertype_draft' => Transfer::DRAFTTRANSFER])->fetchAllAssociative();
    }

    /**
     * @param $ploeg
     * @param null $seizoen
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getUitslagenForPloegForNonDraftTransfersQb(Ploeg $ploeg, $seizoen = null, \DateTime $start = null, \DateTime $end = null)
    {
        if (null === $seizoen) {
            $seizoen = $this->_em->getRepository(Seizoen::class)->getCurrent();
        }
        $parameters = ['ploeg' => $ploeg, 'seizoen' => $seizoen];
        $transfers = $this->_em->getRepository(Transfer::class)->getTransferredInNonDraftRenners($ploeg, $seizoen);

        $qb = $this->createQueryBuilder('u');
        $qb->where('u.ploeg = :ploeg')
            ->join('u.wedstrijd', 'w')
            ->join('u.renner', 'renner')->addSelect('renner')
            ->andWhere('w.seizoen = :seizoen')
            ->andWhere($qb->expr()->in('u.renner', array_merge(array_unique(array_map(function ($a) {
                return $a->getRenner()->getId();
            }, $transfers)), [0])))
            ->andWhere('u.ploegPunten > 0')
            ->setParameters($parameters)
            ->orderBy('w.datum DESC, u.id', 'DESC');
        return $qb;
    }

    /**
     * @param $ploeg
     * @param null $seizoen
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getUitslagenForPloegForLostDraftsQb($ploeg, $seizoen = null, \DateTime $start = null, \DateTime $end = null)
    {
        if (null === $seizoen) {
            $seizoen = $this->_em->getRepository(Seizoen::class)->getCurrent();
        }
        $parameters = ['ploeg' => $ploeg, 'seizoen' => $seizoen];
        $draftrenners = $this->_em->getRepository(Ploeg::class)->getDraftRenners($ploeg);
        $qb = $this->createQueryBuilder('u');
        $qb
            //->where('u.ploeg = :ploeg')
            ->join('u.wedstrijd', 'w')
            ->andWhere('w.seizoen = :seizoen')
            ->andWhere($qb->expr()->in('u.renner', array_merge(array_unique(array_map(function ($r) {
                return $r->getId();
            }, $draftrenners)), [0])))
            ->andWhere('u.rennerPunten > 0')
            //->andWhere('1=1')
            ->andWhere('(u.ploeg != :ploeg OR u.ploeg IS NULL) OR (u.ploeg = :ploeg AND u.ploegPunten = 0)')
            ->setParameters($parameters)
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

    public function getUitslagenForPloegQb($ploeg, $seizoen = null): \Doctrine\ORM\QueryBuilder
    {
        if (null === $seizoen) {
            $seizoen = $this->_em->getRepository(Seizoen::class)->getCurrent();
        }
        $parameters = ['ploeg' => $ploeg, 'seizoen' => $seizoen];
        return $this->createQueryBuilder('u')
            ->join('u.wedstrijd', 'w')
            ->where('u.ploeg = :ploeg')
            ->andWhere('w.seizoen = :seizoen')
            ->andWhere('u.ploegPunten > 0')
            ->setParameters($parameters)
            ->orderBy('w.datum DESC, u.id', 'DESC');
    }

    public function getUitslagenForPloegByPositionQb($ploeg, $position, $seizoen = null): \Doctrine\ORM\QueryBuilder
    {
        if (null === $seizoen) {
            $seizoen = $this->_em->getRepository(Seizoen::class)->getCurrent();
        }
        $parameters = ['ploeg' => $ploeg, 'seizoen' => $seizoen, 'position' => $position];
        return $this->createQueryBuilder('u')
            ->join('u.wedstrijd', 'w')
            ->where('u.ploeg = :ploeg')
            ->andWhere('w.seizoen = :seizoen')
            ->andWhere('u.ploegPunten > 0')
            ->andWhere('u.positie = :position')
            ->setParameters($parameters)
            ->orderBy('w.datum DESC, u.id', 'DESC');
    }

    /**
     * @return array
     */
    public function getBestTransfers(Seizoen $seizoen, \DateTime $start = null, \DateTime $end = null)
    {
        $res = [];
        $ploegen = $this->_em->getRepository(Ploeg::class)->findBy(['seizoen' => $seizoen]);
        foreach ($ploegen as $ploeg) {
            foreach ($this->getUitslagenForPloegForNonDraftTransfersQb($ploeg, $seizoen)
                         ->getQuery()->getResult() as $transferResult) {
                $index = $transferResult->getRenner() . $ploeg->getAfkorting();
                if (!array_key_exists($index, $res)) {
                    $res[$index] = [
                        'rider' => $transferResult->getRenner(),
                        'team' => $transferResult->getPloeg(),
                        'points' => 0,
                    ];
                }
                $res[$index]['points'] += $transferResult->getPloegPunten();
            }
        }
        uasort($res, function ($a, $b) {
            return $a['points'] > $b['points'] ? -1 : 1;
        });
        return $res;
    }
}
