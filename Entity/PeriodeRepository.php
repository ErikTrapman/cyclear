<?php

namespace Cyclear\GameBundle\Entity;

use Doctrine\ORM\EntityRepository;

class PeriodeRepository extends EntityRepository 
{
    
    public function getCurrentPeriode()
    {
        $qb = $this->createQueryBuilder("p")->where("p.eind >= :datum AND p.start <= :datum")->setParameter('datum',new \DateTime());
        return $qb->getQuery()->getSingleResult();
    }
    
    
}
