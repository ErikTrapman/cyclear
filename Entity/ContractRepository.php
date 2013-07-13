<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cyclear\GameBundle\Entity;

class ContractRepository extends \Doctrine\ORM\EntityRepository
{

    public function getCurrentContract($renner, $seizoen)
    {
        $res = $this->getContractsQb($renner, $seizoen)->getQuery()->getResult();
        if (empty($res)) {
            return null;
        }
        return $res[0];
    }

    public function getContracts($renner, $seizoen)
    {
        return $this->getContractsQb($renner, $seizoen)->getQuery()->getResult();
    }

    private function getContractsQb($renner, $seizoen)
    {
        $qb = $this->createQueryBuilder('c')
            ->where('c.renner = :renner')
            ->andWhere('c.seizoen = :seizoen')
            ->andWhere('c.eind IS NULL')
            ->orderBy('c.id', 'DESC');
        $qb->setParameters(array('renner' => $renner, 'seizoen' => $seizoen));
        return $qb;
    }
}