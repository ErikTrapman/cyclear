<?php

/*
 * This file is part of the Cyclear-game package.
 *
 * (c) Erik Trapman <veggatron@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Repository;

class ContractRepository extends \Doctrine\ORM\EntityRepository
{

    public function getCurrentContract($renner, $seizoen)
    {
        $qb = $this->createQueryBuilder('c')
            ->where('c.renner = :renner')
            ->andWhere('c.seizoen = :seizoen')
            ->andWhere('c.eind IS NULL')
            ->orderBy('c.id', 'DESC');
        $qb->setParameters(array('renner' => $renner, 'seizoen' => $seizoen));

        $res = $qb->getQuery()->getResult();
        if (empty($res)) {
            return null;
        }
        return $res[0];
    }

    public function getLastContract($renner, $seizoen, $ploeg = null)
    {
        $qb = $this->createQueryBuilder('c')
            ->where('c.renner = :renner')
            ->andWhere('c.seizoen = :seizoen');
        $qb->setParameters(array('renner' => $renner, 'seizoen' => $seizoen));
        if (null !== $ploeg) {
            $qb->andWhere('c.ploeg = :ploeg');
            $qb->setParameter('ploeg', $ploeg);
        }
        $qb->orderBy('c.id', 'DESC')->setMaxResults(1);
        $res = $qb->getQuery()->getResult();
        if (empty($res)) {
            return null;
        }
        return $res[0];
    }

    public function getLastFinishedContract($renner, $seizoen, $ploeg = null)
    {
        $qb = $this->createQueryBuilder('c')
            ->where('c.renner = :renner')
            ->andWhere('c.seizoen = :seizoen')
            ->andWhere('c.eind IS NOT NULL');
        $qb->setParameters(array('renner' => $renner, 'seizoen' => $seizoen));
        if (null !== $ploeg) {
            $qb->andWhere('c.ploeg = :ploeg');
            $qb->setParameter('ploeg', $ploeg);
        }
        $qb->orderBy('c.id', 'DESC')->setMaxResults(1);
        $res = $qb->getQuery()->getResult();
        if (empty($res)) {
            return null;
        }
        return $res[0];
    }
}