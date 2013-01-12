<?php

namespace Cyclear\GameBundle\Entity;

use Doctrine\ORM\EntityRepository;

class SeizoenRepository extends EntityRepository
{

    public function getCurrent()
    {
        $qb = $this->createQueryBuilder("s")->where("s.current = 1");
        $res = $qb->getQuery()->getResult();
        if(array_key_exists(0, $res)){
            return $res[0];
        }
        return null;
    }
}
