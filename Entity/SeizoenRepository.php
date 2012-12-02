<?php

namespace Cyclear\GameBundle\Entity;

use Doctrine\ORM\EntityRepository;

class SeizoenRepository extends EntityRepository
{

    public function getCurrent()
    {
        $qb = $this->createQueryBuilder("s")->where("s.current = 1");
        return $qb->getQuery()->getSingleResult();
    }
}
