<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\Seizoen;
use Doctrine\ORM\EntityRepository;

class SeizoenRepository extends EntityRepository
{
    public function getCurrent(): ?Seizoen
    {
        return $this->createQueryBuilder('s')->where('s.current = 1')->getQuery()->getOneOrNullResult();
    }
}
