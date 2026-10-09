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
     * @return Award[] oldest season first
     */
    public function findAllWithPloeg(): array
    {
        return $this->createQueryBuilder('a')
            ->addSelect('p', 's', 'u')
            ->innerJoin('a.ploeg', 'p')
            ->innerJoin('p.seizoen', 's')
            ->leftJoin('p.user', 'u')
            ->orderBy('s.id', 'ASC')
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
