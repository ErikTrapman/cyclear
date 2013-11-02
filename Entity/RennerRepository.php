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

use Doctrine\ORM\EntityRepository;

/**
 * RennerRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class RennerRepository extends EntityRepository
{

    public function findOneByNaam($naam)
    {
        return $this->findOneBy(array('naam' => $naam));
    }

    public function findOneByCQId($id)
    {
        return $this->findOneBy(array('cqranking_id' => $id));
    }

    public function findOneBySelectorString($rennerString)
    {
        $firstBracket = strpos($rennerString, '[');
        $lastBracket = strpos($rennerString, ']');
        $cqId = trim(substr($rennerString, 0, $firstBracket));
        $name = substr($rennerString, $firstBracket + 1, $lastBracket - $firstBracket - 1);

        return $this->findOneBy(array('naam' => $name, 'cqranking_id' => $cqId));
    }

    public function getPloeg($renner, $seizoen = null)
    {
        if (null === $seizoen) {
            $seizoen = $this->_em->getRepository("CyclearGameBundle:Seizoen")->getCurrent();
        }
        if (is_numeric($renner)) {
            $renner = $this->_em->getRepository("CyclearGameBundle:Renner")->find($renner);
        }
        $contract = $this->_em->getRepository("CyclearGameBundle:Contract")->getCurrentContract($renner, $seizoen);
        if (null === $contract) {
            return null;
        }
        return $contract->getPloeg();
    }

    public function getPloegOnDate($renner, $seizoen, $date)
    {
        //c.start <= DATE(m.date) AND ( c.end IS NULL OR c.end >= DATE(m.date) )
        $qb = $this->_em->getRepository("CyclearGameBundle:Contract")->createQueryBuilder('c');
        $qb->where('c.renner = :renner')
            ->andWhere('c.start <= DATE(:date) AND ( c.end IS NULL OR c.end >= DATE(:date) )')
            ->andWhere('c.seizoen = :seizoen')
        ;
        $qb->setParameters(array('renner' => $renner, 'date' => $date, 'seizoen' => $seizoen));
        $contracts = $qb->getQuery()->getResult();
        if (empty($contracts)) {
            return null;
        }
        if (count($contracts) > 0) {
            throw new \RuntimeException("Cannot have multiple ploegen from this query");
        }
        return $contracts[0]->getPloeg();
    }

    public function getRennersWithPloeg($seizoen = null)
    {
        if (null === $seizoen) {
            $seizoen = $this->_em->getRepository("CyclearGameBundle:Seizoen")->getCurrent();
        }
        $rennersWithPloeg = array();
        foreach ($this->_em->getRepository("CyclearGameBundle:Contract")
            ->createQueryBuilder('c')
            ->where('c.seizoen = :seizoen')
            ->andWhere('c.eind IS NULL')->setParameter('seizoen', $seizoen)
            ->getQuery()->getResult() as $contract) {
            $rennersWithPloeg [] = $contract->getRenner();
        }
        return $rennersWithPloeg;
    }
}