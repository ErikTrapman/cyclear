<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\Periode;
use App\Entity\Seizoen;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\UnexpectedResultException;
use Doctrine\Persistence\ManagerRegistry;

class PeriodeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, private readonly SeizoenRepository $seizoenRepository)
    {
        parent::__construct($registry, Periode::class);
    }

    public function getCurrentPeriode(Seizoen $seizoen = null): ?Periode
    {
        if (null === $seizoen) {
            $seizoen = $this->seizoenRepository->getCurrent();
        }
        $start = new \DateTime();
        $start->setTime(0, 0, 0);
        $eind = new \DateTime();
        $eind->setTime(23, 59, 59);
        $qb = $this->createQueryBuilder('p')
            ->where('p.eind >= :start AND p.start <= :eind')
            ->andWhere('p.seizoen = :seizoen')->setParameter('start', $start)->setParameter('eind', $eind)->setParameter('seizoen', $seizoen);
        try {
            return $qb->getQuery()->getSingleResult();
        } catch (UnexpectedResultException $e) {
            $periods = $this->createQueryBuilder('p')->orderBy('p.start', 'ASC')->where('p.seizoen = :seizoen')->setParameter('seizoen', $seizoen)->setMaxResults(1)
                ->getQuery()->getResult();
            return $periods[0] ?? null;
        }
    }
}
