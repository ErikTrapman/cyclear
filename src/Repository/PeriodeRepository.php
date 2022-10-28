<?php declare(strict_types=1);

/*
 * This file is part of the Cyclear-game package.
 *
 * (c) Erik Trapman <veggatron@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Repository;

use App\Entity\Seizoen;
use DateTime;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;

class PeriodeRepository extends EntityRepository
{
    public function getCurrentPeriode($seizoen = null)
    {
        if (null === $seizoen) {
            $seizoen = $this->_em->getRepository(Seizoen::class)->getCurrent();
        }
        $start = new DateTime();
        $start->setTime(0, 0, 0);
        $eind = new DateTime();
        $eind->setTime(23, 59, 59);
        $qb = $this->createQueryBuilder('p')
            ->where('p.eind >= :start AND p.start <= :eind')
            ->andWhere('p.seizoen = :seizoen')->setParameter('start', $start)->setParameter('eind', $eind)->setParameter('seizoen', $seizoen);
        try {
            return $qb->getQuery()->getSingleResult();
        } catch (NoResultException $e) {
            $now = new DateTime();
            $now->setTime(0, 0, 0);
            $period = null;
            $periods = $this->createQueryBuilder('p')->orderBy('p.start', 'ASC')->where('p.seizoen = :seizoen')->setParameter('seizoen', $seizoen);
            foreach ($periods->getQuery()->getResult() as $period) {
                if ($period->getEnd() < $now) {
                    continue;
                }
            }
            return $period;
        }
    }
}
