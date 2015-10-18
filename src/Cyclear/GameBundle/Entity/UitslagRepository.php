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
use Doctrine\ORM\Query;

class UitslagRepository extends EntityRepository
{

    public function getPuntenByPloeg($seizoen = null, $ploeg = null, \DateTime $maxDate = null)
    {
        if (null === $seizoen) {
            $seizoen = $this->_em->getRepository("CyclearGameBundle:Seizoen")->getCurrent();
        }
        $params = array('seizoen' => $seizoen);
        $subQuery = $this->createQueryBuilder('u')
            ->select('ifnull(sum(u.ploegPunten),0)')
            ->innerJoin('u.wedstrijd', 'w')->where('w.seizoen = :seizoen')
            ->andWhere('u.ploeg = p')->setParameters(array('seizoen' => $seizoen));
        if (null !== $maxDate) {
            $subQuery->andWhere('w.datum < :maxdate');
            $maxDate->setTime(0, 0, 0);
            //$subQuery->setParameter('maxdate', $maxDate);
            $params['maxdate'] = $maxDate;
        }

        $qb = $this->_em->getRepository('CyclearGameBundle:Ploeg')->createQueryBuilder('p');
        $qb->addSelect('(' . $subQuery->getDQL() . ') AS punten');
        $qb->where('p.seizoen = :seizoen');
        if (null !== $ploeg) {
            $qb->andWhere('p = :ploeg');
            $params['ploeg'] = $ploeg;
        }
        $qb->orderBy('punten', 'DESC, p.afkorting ASC');
        $qb->setParameters($params);
        return $qb->getQuery()->getResult();
    }

    public function getPuntenByPloegForPeriode(Periode $periode, $seizoen = null)
    {
        if (null === $seizoen) {
            $seizoen = $this->_em->getRepository("CyclearGameBundle:Seizoen")->getCurrent();
        }
        $start = clone $periode->getStart();
        $start->setTime('00', '00', '00');
        $end = clone $periode->getEind();
        $end->setTime('23', '59', '59');
        // TODO DQL'en net als getCountForPosition
        $sql = "SELECT *,
                    ( SELECT IFNULL(SUM(u.ploegPunten),0)
                    FROM Uitslag u 
                    INNER JOIN Wedstrijd w ON w.id = u.wedstrijd_id
                    WHERE w.datum BETWEEN :start AND :end AND u.ploeg_id = p.id
                     ) AS punten
                FROM Ploeg p WHERE p.seizoen_id = :seizoen_id
                GROUP BY p.id
                ORDER BY punten DESC, p.afkorting ASC
                ";
        $conn = $this->getEntityManager()->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->execute(array(":seizoen_id" => $seizoen->getId(), ":start" => $start->format('Y-m-d'), ":end" => $end->format('Y-m-d')));
        $res = $stmt->fetchAll(\PDO::FETCH_NAMED);
        return $res;
    }

    public function getCountForPosition($seizoen = null, $pos = 1, \DateTime $start = null, \DateTime $end = null)
    {
        if (null === $seizoen) {
            $seizoen = $this->_em->getRepository("CyclearGameBundle:Seizoen")->getCurrent();
        }
        $parameters = array('seizoen' => $seizoen, 'pos' => $pos);
        $qb2 = $this->createQueryBuilder('u')
            ->select('SUM(IF(u.positie = :pos,1,0))')
            ->join('u.wedstrijd', 'w')
            ->where('u.ploeg = p.id')
            ->andWhere('w.seizoen = :seizoen')
            ->andWhere('u.ploegPunten > 0');
        if ($start && $end) {
            $start = clone $start;
            $start->setTime(0, 0, 0);
            $end = clone $end;
            $end->setTime(23, 59, 59);
            $qb2->andWhere('w.datum >= :start AND w.datum <= :end');
            $parameters['start'] = $start;
            $parameters['end'] = $end;
        }
        $qb = $this->_em->getRepository('CyclearGameBundle:Ploeg')->createQueryBuilder('p');
        $qb
            ->where('p.seizoen = :seizoen')
            ->addSelect(sprintf('IFNULL((%s),0) as freqByPos', $qb2->getDql()))
            ->groupBy('p.id')
            ->orderBy('freqByPos DESC, p.afkorting', 'ASC')
            ->setParameters($parameters);;
        return $qb->getQuery()->getResult();
    }

    public function getPuntenForRenner($renner, $seizoen = null, $excludeZeros = false)
    {
        if (null === $seizoen) {
            $seizoen = $this->_em->getRepository("CyclearGameBundle:Seizoen")->getCurrent();
        }
        $qb = $this->getPuntenForRennerQb($renner);
        $qb->andWhere("w.seizoen = :seizoen");
        if ($excludeZeros) {
            $qb->andWhere('u.rennerPunten > 0');
        }
        $qb->setParameters(array('seizoen' => $seizoen, 'renner' => $renner));
        return $qb->getQuery()->getResult();
    }

    public function getTotalPuntenForRenner($renner, $seizoen = null)
    {
        if (null === $seizoen) {
            $seizoen = $this->_em->getRepository("CyclearGameBundle:Seizoen")->getCurrent();
        }
        $qb = $this->getPuntenForRennerQb($renner);
        $qb->andWhere("w.seizoen = :seizoen");
        $qb->setParameters(array('seizoen' => $seizoen, 'renner' => $renner));
        $qb->add('select', 'SUM(u.rennerPunten)');
        return $qb->getQuery()->getSingleScalarResult();
    }

    public function getPuntenForRennerWithPloeg($renner, $ploeg, $seizoen = null)
    {
        if (null === $seizoen) {
            $seizoen = $this->_em->getRepository("CyclearGameBundle:Seizoen")->getCurrent();
        }
        $qb = $this->getPuntenForRennerQb($renner);
        $qb->andWhere("w.seizoen = :seizoen")->andWhere('u.ploeg = :ploeg');
        $qb->setParameters(array('seizoen' => $seizoen, 'ploeg' => $ploeg, 'renner' => $renner));
        $qb->add('select', 'SUM(u.ploegPunten)');
        return $qb->getQuery()->getSingleScalarResult();
    }

    private function getPuntenForRennerQb()
    {
        $qb = $this->createQueryBuilder("u")
            ->join('u.wedstrijd', 'w')
            ->where("u.renner = :renner")
            ->orderBy("u.id", "DESC");
        return $qb;
    }

    public function getPuntenWithRenners($seizoen = null, $limit = 20)
    {
        if (null === $seizoen) {
            $seizoen = $this->_em->getRepository("CyclearGameBundle:Seizoen")->getCurrent();
        }
        $qb = $this->createQueryBuilder('u')
            ->join('u.wedstrijd', 'w')
            ->where('w.seizoen =:seizoen')
            ->leftJoin('u.renner', 'r')
            ->groupBy('u.renner')->add('select', 'IFNULL(SUM(u.rennerPunten),0) AS punten', true)
            ->setMaxResults($limit)
            ->setParameters(array('seizoen' => $seizoen))
            ->orderBy('punten DESC, r.naam', 'ASC');
        $ret = array();
        foreach ($qb->getQuery()->getResult() as $result) {
            $ret[] = array(0 => $result[0]->getRenner(), 'punten' => $result['punten']);
        }
        return $ret;
    }

    public function getPuntenWithRennersNoPloeg($seizoen = null, $limit = 20)
    {
        if (null === $seizoen) {
            $seizoen = $this->_em->getRepository("CyclearGameBundle:Seizoen")->getCurrent();
        }
        $rennersWithPloeg = array();
        foreach ($this->_em->getRepository("CyclearGameBundle:Renner")->getRennersWithPloeg() as $renner) {
            $rennersWithPloeg [] = $renner->getId();
        }
        $qb = $this->createQueryBuilder('u')
            ->join('u.wedstrijd', 'w')
            ->where('w.seizoen =:seizoen')
            ->leftJoin('u.renner', 'r')
            ->groupBy('u.renner')->add('select', 'IFNULL(SUM(u.rennerPunten),0) AS punten', true)
            ->setMaxResults($limit)
            ->setParameters(array('seizoen' => $seizoen))
            ->orderBy('punten DESC, r.naam', 'ASC');
        if (!empty($rennersWithPloeg)) {
            $qb->andWhere($qb->expr()->notIn('u.renner', $rennersWithPloeg));
        }
        $ret = array();
        foreach ($qb->getQuery()->getResult() as $result) {
            $ret[] = array(0 => $result[0]->getRenner(), 'punten' => $result['punten']);
        }
        return $ret;
    }

    public function getPuntenByPloegForDraftTransfers($seizoen = null, $ploeg = null)
    {
        if (null === $seizoen) {
            $seizoen = $this->_em->getRepository("CyclearGameBundle:Seizoen")->getCurrent();
        }
        // TODO DQL'en net als getCountForPosition
        $transferSql = "SELECT t.renner_id FROM Transfer t 
            WHERE t.transferType = " . Transfer::DRAFTTRANSFER . " AND t.ploegNaar_id = p.id AND t.seizoen_id = :seizoen_id";
        $ploegWhere = '';
        $params = array(":seizoen_id" => $seizoen->getId(), 'transfertype_draft' => Transfer::DRAFTTRANSFER);
        if (null !== $ploeg) {
            $ploegWhere = ' AND p.id = :ploeg';
            $params[':ploeg'] = $ploeg->getId();
        }
        $sql = sprintf("SELECT p.id AS id, p.naam AS naam, p.afkorting AS afkorting,
                ( SELECT IFNULL(SUM(u.rennerPunten),0) FROM Uitslag u 
                INNER JOIN Wedstrijd w ON u.wedstrijd_id = w.id 
                WHERE w.seizoen_id = :seizoen_id AND u.renner_id IN ( %s ) ) AS punten 
                FROM Ploeg p WHERE p.seizoen_id = :seizoen_id %s
                ORDER BY punten DESC, p.afkorting ASC
                ", $transferSql, $ploegWhere);
        $conn = $this->getEntityManager()->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_NAMED);
    }


    public function getPuntenByPloegForUserTransfersWithoutLoss($seizoen = null, \DateTime $start = null, \DateTime $end = null)
    {
        if (null === $seizoen) {
            $seizoen = $this->_em->getRepository("CyclearGameBundle:Seizoen")->getCurrent();
        }
        $params = array(":seizoen_id" => $seizoen->getId(), 'transfertype_draft' => Transfer::DRAFTTRANSFER);
        $startEndWhere = null;
        if ($start && $end) {
            $startEndWhere = ' AND (w.datum >= :start AND w.datum <= :end)';
            $start = clone $start;
            $start->setTime(0, 0, 0);
            $end = clone $end;
            $end->setTime(0, 0, 0);
            $params['start'] = $start->format('Y-m-d H:i:s');
            $params['end'] = $end->format('Y-m-d H:i:s');
        }

        $this->_em->getRepository('CyclearGameBundle:Transfer')->generateTempTableWithDraftRiders($seizoen);

        // TODO DQL'en net als getCountForPosition
        $transfers = "SELECT DISTINCT t.renner_id FROM Transfer t
            WHERE t.transferType != " . Transfer::DRAFTTRANSFER . " AND t.ploegNaar_id = p.id AND t.seizoen_id = :seizoen_id
                AND t.renner_id NOT IN
                (SELECT t.renner_id FROM Transfer t
                WHERE t.transferType = " . Transfer::DRAFTTRANSFER . " AND t.ploegNaar_id = p.id
                AND t.seizoen_id = :seizoen_id)";


        $sql = sprintf("
                SELECT p.id AS id, p.naam AS naam, p.afkorting AS afkorting, 100 AS b,
                (

                (SELECT IFNULL(SUM(u.ploegPunten),0)
                FROM Uitslag u
                INNER JOIN Wedstrijd w ON u.wedstrijd_id = w.id
                WHERE w.seizoen_id = :seizoen_id %s AND u.ploeg_id = p.id AND u.renner_id IN (%s))

                ) AS punten

                FROM Ploeg p WHERE p.seizoen_id = :seizoen_id
                ORDER BY punten DESC, p.afkorting ASC
                ", $startEndWhere, $transfers);
        $conn = $this->getEntityManager()->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_NAMED);
    }

    public function getLostDraftPuntenByPloeg($seizoen = null, \DateTime $start = null, \DateTime $end = null)
    {
        if (null === $seizoen) {
            $seizoen = $this->_em->getRepository("CyclearGameBundle:Seizoen")->getCurrent();
        }
        $params = array(":seizoen_id" => $seizoen->getId(), 'transfertype_draft' => Transfer::DRAFTTRANSFER);
        $startEndWhere = null;
        if ($start && $end) {
            $startEndWhere = ' AND (w.datum >= :start AND w.datum <= :end)';
            $start = clone $start;
            $start->setTime(0, 0, 0);
            $end = clone $end;
            $end->setTime(0, 0, 0);
            $params['start'] = $start->format('Y-m-d H:i:s');
            $params['end'] = $end->format('Y-m-d H:i:s');
        }

        $this->_em->getRepository('CyclearGameBundle:Transfer')->generateTempTableWithDraftRiders($seizoen);

        $sql = sprintf("SELECT p.id AS id, p.naam AS naam, p.afkorting AS afkorting, 200 AS c,
                (
                SELECT IFNULL(SUM(u.rennerPunten),0) FROM Uitslag u
                INNER JOIN Wedstrijd w ON u.wedstrijd_id = w.id WHERE w.seizoen_id = :seizoen_id %s
                AND u.renner_id IN
                    (SELECT t.renner_id FROM Transfer t
                    WHERE t.transferType = " . Transfer::DRAFTTRANSFER . "
                    AND t.ploegNaar_id = p.id AND t.seizoen_id = :seizoen_id)
                AND (u.ploeg_id IS NULL OR u.ploeg_id <> p.id)
                ) AS punten

                FROM Ploeg p WHERE p.seizoen_id = :seizoen_id
                ORDER BY punten DESC, p.afkorting ASC
                ", $startEndWhere);
        $conn = $this->getEntityManager()->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_NAMED);
    }

    public function getPuntenByPloegForUserTransfers($seizoen = null)
    {
        if (null === $seizoen) {
            $seizoen = $this->_em->getRepository("CyclearGameBundle:Seizoen")->getCurrent();
        }
        $this->_em->getRepository('CyclearGameBundle:Transfer')->generateTempTableWithDraftRiders($seizoen);

        // TODO DQL'en net als getCountForPosition
        $transfers = "SELECT DISTINCT t.renner_id FROM Transfer t 
            WHERE t.transferType != " . Transfer::DRAFTTRANSFER . " AND t.ploegNaar_id = p.id AND t.seizoen_id = :seizoen_id
                AND t.renner_id NOT IN ( SELECT t.renner_id FROM Transfer t WHERE t.transferType = " . Transfer::DRAFTTRANSFER . " AND t.ploegNaar_id = p.id AND t.seizoen_id = :seizoen_id )";
        $sql = sprintf("SELECT p.id AS id, p.naam AS naam, p.afkorting AS afkorting,
                ((SELECT IFNULL(SUM(u.ploegPunten),0)
                FROM Uitslag u
                INNER JOIN Wedstrijd w ON u.wedstrijd_id = w.id
                WHERE w.seizoen_id = :seizoen_id AND u.ploeg_id = p.id AND u.renner_id IN (%s))

                -

                (SELECT IFNULL(SUM(u.rennerPunten),0)
                FROM Uitslag u
                INNER JOIN Wedstrijd w ON u.wedstrijd_id = w.id AND w.seizoen_id = :seizoen_id
                INNER JOIN draftriders dr ON u.renner_id = dr.renner_id
                WHERE dr.ploeg_id = p.id AND (u.ploeg_id IS NULL OR u.ploeg_id <> p.id))

                ) AS punten

                FROM Ploeg p WHERE p.seizoen_id = :seizoen_id
                ORDER BY punten DESC, p.afkorting ASC
                ", $transfers);
        $conn = $this->getEntityManager()->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->execute(array(":seizoen_id" => $seizoen->getId(), 'transfertype_draft' => Transfer::DRAFTTRANSFER));
        return $stmt->fetchAll(\PDO::FETCH_NAMED);
    }

    public function getUitslagenForPloegForNonDraftTransfersQb($ploeg, $seizoen = null)
    {
        if (null === $seizoen) {
            $seizoen = $this->_em->getRepository("CyclearGameBundle:Seizoen")->getCurrent();
        }
        $parameters = array('ploeg' => $ploeg, 'seizoen' => $seizoen);
        $transfers = $this->_em->getRepository('CyclearGameBundle:Transfer')->getTransferredInNonDraftRenners($ploeg, $seizoen);

        $qb = $this->createQueryBuilder('u');
        $qb->where('u.ploeg = :ploeg')
            ->join('u.wedstrijd', 'w')
            ->andWhere('w.seizoen = :seizoen')
            ->andWhere($qb->expr()->in('u.renner', array_merge(array_unique(array_map(function ($a) {
                return $a->getRenner()->getId();
            }, $transfers)), array(0))))
            ->andWhere('u.ploegPunten > 0')
            ->setParameters($parameters)
            ->orderBy('w.datum DESC, u.id', 'DESC');
        return $qb;
    }

    public function getUitslagenForPloegForLostDraftsQb($ploeg, $seizoen = null)
    {
        if (null === $seizoen) {
            $seizoen = $this->_em->getRepository("CyclearGameBundle:Seizoen")->getCurrent();
        }
        $parameters = array('ploeg' => $ploeg, 'seizoen' => $seizoen);
        $draftrenners = $this->_em->getRepository("CyclearGameBundle:Ploeg")->getDraftRenners($ploeg);
        $qb = $this->createQueryBuilder('u');
        $qb
            //->where('u.ploeg = :ploeg')
            ->join('u.wedstrijd', 'w')
            ->andWhere('w.seizoen = :seizoen')
            ->andWhere($qb->expr()->in('u.renner', array_merge(array_unique(array_map(function ($r) {
                return $r->getId();
            }, $draftrenners)), array(0))))
            ->andWhere('u.rennerPunten > 0')
            //->andWhere('1=1')
            ->andWhere('(u.ploeg != :ploeg OR u.ploeg IS NULL)')
            ->setParameters($parameters)
            ->orderBy('w.datum DESC, u.id', 'DESC');
        return $qb;
    }

    public function getUitslagenForPloegQb($ploeg, $seizoen = null)
    {
        if (null === $seizoen) {
            $seizoen = $this->_em->getRepository("CyclearGameBundle:Seizoen")->getCurrent();
        }
        $parameters = array('ploeg' => $ploeg, 'seizoen' => $seizoen);
        return $this->createQueryBuilder('u')
            ->join('u.wedstrijd', 'w')
            ->where('u.ploeg = :ploeg')
            ->andWhere('w.seizoen = :seizoen')
            ->andWhere('u.ploegPunten > 0')
            ->setParameters($parameters)
            ->orderBy('w.datum DESC, u.id', 'DESC');
    }

    public function getUitslagenForPloegByPositionQb($ploeg, $position, $seizoen = null)
    {
        if (null === $seizoen) {
            $seizoen = $this->_em->getRepository("CyclearGameBundle:Seizoen")->getCurrent();
        }
        $parameters = array('ploeg' => $ploeg, 'seizoen' => $seizoen, 'position' => $position);
        return $this->createQueryBuilder('u')
            ->join('u.wedstrijd', 'w')
            ->where('u.ploeg = :ploeg')
            ->andWhere('w.seizoen = :seizoen')
            ->andWhere('u.ploegPunten > 0')
            ->andWhere('u.positie = :position')
            ->setParameters($parameters)
            ->orderBy('w.datum DESC, u.id', 'DESC');
    }
}
