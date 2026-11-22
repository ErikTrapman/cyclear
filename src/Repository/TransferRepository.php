<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\Ploeg;
use App\Entity\Renner;
use App\Entity\Seizoen;
use App\Entity\Transfer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class TransferRepository extends ServiceEntityRepository
{
    public const CACHE_TAG = 'TransferRepository';

    public function __construct(
        ManagerRegistry $registry,
        private readonly TagAwareCacheInterface $cache,
        private readonly SeizoenRepository $seizoenRepository,
        private readonly PloegRepository $ploegRepository,
    ) {
        parent::__construct($registry, Transfer::class);
    }

    public function findByRenner(Renner $renner, $seizoen = null, $types = [])
    {
        $seizoen = $this->resolveSeizoen($seizoen);

        $qb = $this->getQueryBuilderForRenner($renner);
        $qb->andWhere('t.seizoen = ?2');
        $qb->setParameter('2', $seizoen);
        $qb->orderBy('t.datum DESC, t.id', 'DESC');
        if (!empty($types)) {
            $qb->andWhere('t.transferType IN ( :types )')->setParameter('types', $types);
        }
        return $qb->getQuery()->getResult();
    }

    private function getQueryBuilderForRenner(Renner $renner): \Doctrine\ORM\QueryBuilder
    {
        $qb = $this->createQueryBuilder('t');
        $qb->where('t.renner = ?1');
        $qb->setParameter('1', $renner);
        return $qb;
    }

    public function getLatest($seizoen = null, $types = [], $limit = 20, $ploegNaar = null, $renner = null)
    {
        $seizoen = $this->resolveSeizoen($seizoen);
        // fetch-join everything the transfer lists render, so they don't lazy-load per row
        $qb = $this
            ->createQueryBuilder('t')
            ->leftJoin('t.ploegNaar', 'ploegNaar')->addSelect('ploegNaar')
            ->leftJoin('t.renner', 'renner')->addSelect('renner')
            ->leftJoin('renner.country', 'rennerCountry')->addSelect('rennerCountry')
            ->leftJoin('t.inversionTransfer', 'inversion')->addSelect('inversion')
            ->leftJoin('inversion.renner', 'inversionRenner')->addSelect('inversionRenner')
            ->leftJoin('inversionRenner.country', 'inversionCountry')->addSelect('inversionCountry')
            ->where('t.ploegNaar IS NOT NULL')
            ->andWhere('t.seizoen = :seizoen')
            ->setParameter('seizoen', $seizoen)
            ->setMaxResults($limit)
            ->orderBy('t.datum', 'DESC');
        if (null !== $ploegNaar) {
            $qb->andWhere('t.ploegNaar = :ploegNaar')->setParameter('ploegNaar', $ploegNaar);
        }
        if (null !== $renner) {
            $qb->andWhere('t.renner = :renner')->setParameter('renner', $renner);
        }
        if (!empty($types)) {
            $qb->andWhere('t.transferType IN ( :types )')->setParameter('types', $types);
        }
        return $qb->getQuery()->getResult(\Doctrine\ORM\AbstractQuery::HYDRATE_OBJECT);
    }

    public function getTransferCountForUserTransfer($ploeg, $start, $end)
    {
        return $this->getTransferCountByType($ploeg, $start, $end, [Transfer::USERTRANSFER, Transfer::ADMINTRANSFER]);
    }

    public function getTransferCountByType($ploeg, $start, $end, array $type): int
    {
        if (is_numeric($ploeg)) {
            $ploeg = $this->ploegRepository->find($ploeg);
        }
        $key = __FUNCTION__ . $ploeg->getId() . $start?->format('YmdHis') . $end?->format('YmdHis') . implode('', $type);
        $item = $this->cache->getItem($key);
        if ($item->isHit()) {
            return $item->get();
        }
        $cloneEnd = clone $end;
        $cloneEnd->setTime(23, 59, 59);
        $cloneStart = clone $start;
        $cloneStart->setTime(0, 0, 0);
        $query = $this->getEntityManager()
            ->createQuery('SELECT COUNT(t.id) AS freq FROM App\\Entity\\Transfer t
                WHERE t.ploegNaar = :ploeg AND t.datum BETWEEN :start AND :end AND t.transferType IN( :type )')
            ->setParameters(['type' => $type, 'ploeg' => $ploeg, 'start' => $cloneStart, 'end' => $cloneEnd]);
        $res = $query->getSingleResult();
        $count = (int)$res['freq'];
        $item->set($count);
        $item->tag(self::CACHE_TAG);
        $this->cache->save($item);
        return $count;
    }

    public function findLastTransferForDate($renner, \DateTime $date, $seizoen)
    {
        $cloneDate = clone $date;
        $cloneDate->setTime(23, 59, 59);
        $params = ['renner' => $renner, 'datum' => $cloneDate, 'seizoen' => $seizoen];
        $qb = $this->createQueryBuilder('t')
            ->where('t.renner = :renner')
            ->andWhere('t.datum <= :datum')->andWhere('t.seizoen = :seizoen')->
            setParameters(Util::buildParameters($params))->orderBy('t.datum', 'DESC')->setMaxResults(1);
        $res = $qb->getQuery()->getResult();
        if (0 == count($res)) {
            return null;
        }
        return $res[0];
    }

    public function getTransferredInNonDraftRenners($ploeg, $seizoen = null)
    {
        $seizoen = $this->resolveSeizoen($seizoen);
        $tQb = $this->createQueryBuilder('t');

        $draftrenners = $this->ploegRepository->getDraftRenners($ploeg);
        $params = [
            'drafttransfer' => Transfer::DRAFTTRANSFER,
            'ploeg' => $ploeg,
            'seizoen' => $seizoen,
        ];

        $qb2 = $this->createQueryBuilder('t2')
            ->where('t2.transferType = :drafttransfer')
            ->andWhere('t2.ploegNaar = :ploeg')
            ->andWhere('t2.seizoen = :seizoen');
        if (!empty($draftrenners)) {
            $qb2->orWhere('t2.renner IN (:draftrenners)');
            $params['draftrenners'] = $draftrenners;
        }
        $qb2->setParameters(Util::buildParameters($params));

        $tQb
            ->where('t.transferType != :drafttransfer')
            ->andWhere('t.ploegNaar = :ploeg')
            ->andWhere('t.seizoen = :seizoen')
            ->andWhere($tQb->expr()->notIn('t', $qb2->getDql()))
            ->setParameters(Util::buildParameters($params));
        return $tQb->getQuery()->getResult();
    }

    /**
     * The drafted riders of every team in a season, in one query.
     *
     * @return array<int, array<int, true>> rider ids as keys, per team id
     */
    public function getDraftRiderIdsPerTeam(Seizoen $seizoen): array
    {
        $rows = $this->createQueryBuilder('t')
            ->select('IDENTITY(t.ploegNaar) AS ploeg, IDENTITY(t.renner) AS renner')
            ->innerJoin('t.ploegNaar', 'p')
            ->where('t.transferType = :draft')
            ->andWhere('p.seizoen = :seizoen')
            ->setParameter('draft', Transfer::DRAFTTRANSFER)
            ->setParameter('seizoen', $seizoen)
            ->getQuery()->getScalarResult();
        $perTeam = [];
        foreach ($rows as $row) {
            $perTeam[(int)$row['ploeg']][(int)$row['renner']] = true;
        }
        return $perTeam;
    }

    /**
     * Per team, the riders it transferred in during a season without having drafted them:
     * getTransferredInNonDraftRenners() for every team at once.
     *
     * @return array<int, array<int, true>> rider ids as keys, per team id
     */
    public function getNonDraftRiderIdsPerTeam(Seizoen $seizoen): array
    {
        $rows = $this->createQueryBuilder('t')
            ->select('DISTINCT IDENTITY(t.ploegNaar) AS ploeg, IDENTITY(t.renner) AS renner')
            ->innerJoin('t.ploegNaar', 'p')
            ->where('t.transferType != :draft')
            ->andWhere('t.seizoen = :seizoen')
            ->andWhere('p.seizoen = :seizoen')
            ->setParameter('draft', Transfer::DRAFTTRANSFER)
            ->setParameter('seizoen', $seizoen)
            ->getQuery()->getScalarResult();
        $draftRiderIds = $this->getDraftRiderIdsPerTeam($seizoen);
        $perTeam = [];
        foreach ($rows as $row) {
            $ploegId = (int)$row['ploeg'];
            $rennerId = (int)$row['renner'];
            if (!isset($draftRiderIds[$ploegId][$rennerId])) {
                $perTeam[$ploegId][$rennerId] = true;
            }
        }
        return $perTeam;
    }

    /**
     * getTransferCountByType() for every team of a season, in one query.
     *
     * @return array<int, int> transfer count per team id; teams without transfers are left out
     */
    public function getTransferCountsPerTeam(Seizoen $seizoen, \DateTime $start, \DateTime $end, array $types = [Transfer::USERTRANSFER, Transfer::ADMINTRANSFER]): array
    {
        $key = __FUNCTION__ . $seizoen->getId() . $start->format('YmdHis') . $end->format('YmdHis') . implode('', $types);
        $item = $this->cache->getItem($key);
        if ($item->isHit()) {
            return $item->get();
        }
        $cloneStart = clone $start;
        $cloneStart->setTime(0, 0, 0);
        $cloneEnd = clone $end;
        $cloneEnd->setTime(23, 59, 59);
        $rows = $this->createQueryBuilder('t')
            ->select('IDENTITY(t.ploegNaar) AS ploeg, COUNT(t.id) AS freq')
            ->innerJoin('t.ploegNaar', 'p')
            ->where('p.seizoen = :seizoen')
            ->andWhere('t.datum BETWEEN :start AND :end')
            ->andWhere('t.transferType IN (:types)')
            ->groupBy('t.ploegNaar')
            ->setParameter('seizoen', $seizoen)
            ->setParameter('start', $cloneStart)
            ->setParameter('end', $cloneEnd)
            ->setParameter('types', $types)
            ->getQuery()->getScalarResult();
        $counts = [];
        foreach ($rows as $row) {
            $counts[(int)$row['ploeg']] = (int)$row['freq'];
        }
        $item->set($counts);
        $item->tag(self::CACHE_TAG);
        $this->cache->save($item);
        return $counts;
    }

    public function hasDraftTransfer(Renner $renner, Ploeg $ploeg)
    {
        return $this->createQueryBuilder('t')
            ->where('t.renner = :rider')
            ->andWhere('t.ploegNaar = :team')
            ->andWhere('t.transferType = :type')
            ->setParameters(Util::buildParameters(['rider' => $renner, 'team' => $ploeg, 'type' => Transfer::DRAFTTRANSFER]))
            ->getQuery()
            ->getOneOrNullResult();
    }

    private function resolveSeizoen(?Seizoen $seizoen = null): Seizoen
    {
        if (null === $seizoen) {
            $seizoen = $this->seizoenRepository->getCurrent();
        }
        return $seizoen;
    }
}
