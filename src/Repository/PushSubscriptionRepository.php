<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\PushSubscription;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PushSubscription>
 */
class PushSubscriptionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PushSubscription::class);
    }

    /**
     * @return PushSubscription[]
     */
    public function findAllExceptUser(?User $user): array
    {
        $qb = $this->createQueryBuilder('s');
        if (null !== $user) {
            $qb->where('s.user != :user')->setParameter('user', $user);
        }
        return $qb->getQuery()->getResult();
    }
}
