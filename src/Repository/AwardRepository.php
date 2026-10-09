<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\Award;
use App\Entity\AwardType;
use App\Entity\Seizoen;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Award>
 */
class AwardRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Award::class);
    }

    /**
     * Awards without a ploeg come from an edition before the seasons in this database, so they are listed first.
     *
     * @return Award[] oldest season first
     */
    public function findAllWithPloeg(): array
    {
        return $this->createQueryBuilder('a')
            ->addSelect('p', 's', 'u', 'au')
            ->addSelect('CASE WHEN p.id IS NULL THEN 0 ELSE 1 END AS HIDDEN withPloeg')
            ->leftJoin('a.ploeg', 'p')
            ->leftJoin('p.seizoen', 's')
            ->leftJoin('p.user', 'u')
            ->leftJoin('a.user', 'au')
            ->orderBy('withPloeg', 'ASC')
            ->addOrderBy('a.season', 'ASC')
            ->addOrderBy('s.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function hasAwardInSeizoen(Seizoen $seizoen, AwardType $type): bool
    {
        return null !== $this->createQueryBuilder('a')
            ->select('a.id')
            ->innerJoin('a.ploeg', 'p')
            ->where('p.seizoen = :seizoen')
            ->andWhere('a.type = :type')
            ->setParameter('seizoen', $seizoen)
            ->setParameter('type', $type)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
