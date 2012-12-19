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
        $qb = $this->createQueryBuilder('c')->where('c.renner = :renner')->andWhere('c.seizoen = :seizoen')->andWhere('c.eind IS NOT NULL');
        $qb->setParameters(array('renner' => $renner, 'seizoen' => $seizoen));
        $res = $qb->getQuery()->getResult();
        if (empty($res)) {
            return null;
        }
        return $res[0];
    }
}