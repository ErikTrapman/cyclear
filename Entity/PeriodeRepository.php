<?php

namespace Cyclear\GameBundle\Entity;

use DateTime;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;

class PeriodeRepository extends EntityRepository 
{
    
    public function getCurrentPeriode()
    {
        $start = new DateTime();
        $start->setTime(0, 0, 0);
        $eind = new DateTime();
        $eind->setTime(23, 59, 59);
        $qb = $this->createQueryBuilder("p")->where("p.eind >= :start AND p.start <= :eind")->setParameter('start',$start)->setParameter('eind', $eind);
        try {
            return $qb->getQuery()->getSingleResult();
        } catch (NoResultException $e) {
            $qb = $this->createQueryBuilder("p")->where("p.start <= :eind")->setParameter('eind', $eind)->orderBy('p.eind','DESC')->setMaxResults(1);
            return $qb->getQuery()->getSingleResult();
        }
    }
    
    
}
