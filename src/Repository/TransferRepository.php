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
        $qb = $this
            ->createQueryBuilder('t')
            ->where('t.ploegNaar IS NOT NULL')
            ->andWhere('t.seizoen = :seizoen')
            ->setParameters(['seizoen' => $seizoen])
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
            setParameters($params)->orderBy('t.datum', 'DESC')->setMaxResults(1);
        $res = $qb->getQuery()->getResult();
        if (count($res) == 0) {
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
        $qb2->setParameters($params);

        $tQb
            ->where('t.transferType != :drafttransfer')
            ->andWhere('t.ploegNaar = :ploeg')
            ->andWhere('t.seizoen = :seizoen')
            ->andWhere($tQb->expr()->notIn('t', $qb2->getDql()))
            ->setParameters($params);
        return $tQb->getQuery()->getResult();
    }

    /**
     * Creates a temporary table following this scheme:
     * CREATE TEMPORARY TABLE IF NOT EXISTS $tableName (ploeg_id int, renner_id int)
     */
    public function generateTempTableWithDraftRiders(Seizoen $seizoen, string $tableName = 'draftriders'): void
    {
        $conn = $this->_em->getConnection();
        $conn->executeQuery("DROP TABLE IF EXISTS $tableName; CREATE TEMPORARY TABLE " . $tableName . ' (ploeg_id int, renner_id int) ENGINE=MEMORY');
        $conn->executeQuery('INSERT INTO ' . $tableName . '
          ( SELECT ploegNaar_id, renner_id FROM transfer t
          WHERE t.transferType = ' . Transfer::DRAFTTRANSFER . ' AND t.seizoen_id = ' . $seizoen->getId() . ' )');
    }

    public function generateTempTableWithTransferredRiders(Seizoen $seizoen, string $tableName = 'transferriders'): void
    {
        $this->generateTempTableWithDraftRiders($seizoen);
        $conn = $this->_em->getConnection();
        $conn->executeQuery("DROP TABLE IF EXISTS $tableName; CREATE TEMPORARY TABLE IF NOT EXISTS " . $tableName . ' (ploeg_id int, renner_id int) ENGINE=MEMORY');

        $seizoenId = $seizoen->getId();

        $a = "SELECT t.renner_id, t.ploegNaar_id
                FROM transfer t
                WHERE t.renner_id NOT IN ( SELECT dr.renner_id FROM draftriders dr WHERE dr.ploeg_id = t.ploegNaar_id )
                AND t.seizoen_id = $seizoenId AND t.ploegNaar_id IS NOT NULL AND t.transferType <> " . Transfer::DRAFTTRANSFER . '
                GROUP BY t.renner_id, t.ploegNaar_id
                ORDER BY t.ploegNaar_id';
        $conn->executeQuery("INSERT INTO $tableName (" . $a . ')');
    }

    public function hasDraftTransfer(Renner $renner, Ploeg $ploeg)
    {
        return $this->createQueryBuilder('t')
            ->where('t.renner = :rider')
            ->andWhere('t.ploegNaar = :team')
            ->andWhere('t.transferType = :type')
            ->setParameters(['rider' => $renner, 'team' => $ploeg, 'type' => Transfer::DRAFTTRANSFER])
            ->getQuery()
            ->getOneOrNullResult();
    }

    private function resolveSeizoen(Seizoen $seizoen = null): Seizoen
    {
        if (null === $seizoen) {
            $seizoen = $this->seizoenRepository->getCurrent();
        }
        return $seizoen;
    }
}
