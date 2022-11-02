<?php declare(strict_types=1);

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

class SeizoenRepository extends EntityRepository
{
    /**
     * @return Seizoen
     */
    public function getCurrent()
    {
        return $this->createQueryBuilder('s')->where('s.current = 1')->getQuery()->getOneOrNullResult();
    }
}
