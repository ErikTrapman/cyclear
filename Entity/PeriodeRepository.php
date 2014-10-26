<?php

/*
 * This file is part of the Cyclear-game package.
 *
 * (c) Erik Trapman <veggatron@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cyclear\GameBundle\Entity;

use DateTime;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;

class PeriodeRepository extends EntityRepository 
{
    
    public function getCurrentPeriode($seizoen = null)
    {
        if (null === $seizoen) {
            $seizoen = $this->_em->getRepository("CyclearGameBundle:Seizoen")->getCurrent();
        }
        $start = new DateTime();
        $start->setTime(0, 0, 0);
        $eind = new DateTime();
        $eind->setTime(23, 59, 59);
        $qb = $this->createQueryBuilder("p")
            ->where("p.eind >= :start AND p.start <= :eind")
            ->andWhere('p.seizoen = :seizoen')->setParameter('start',$start)->setParameter('eind', $eind)->setParameter('seizoen', $seizoen);
        try {
            return $qb->getQuery()->getSingleResult();
        } catch (NoResultException $e) {
            $qb = $this->createQueryBuilder('p')->orderBy('p.start','DESC')
                ->where('p.seizoen = :seizoen')
                ->setParameter('seizoen', $seizoen)
                ->setMaxResults(1);
            return $qb->getQuery()->getSingleResult();
        }
    }
    
    
}
