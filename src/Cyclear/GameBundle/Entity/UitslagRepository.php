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

    /**
     * @param $values
     * @param string $fallBackSort
     */
    public static function puntenSort(&$values, $fallBackSort = 'afkorting')
    {
        uasort($values, function ($a, $b) use ($fallBackSort) {
            $aPoints = null;
            $bPoints = null;
            if ($a instanceof Ploeg && $b instanceof Ploeg) {
                $aPoints = $a->getPunten();
                $bPoints = $b->getPunten();
            } else {
                $aPoints = $a['punten'];
                $bPoints = $b['punten'];
            }
            if ($aPoints == $bPoints) {
                if ($a instanceof Ploeg && $b instanceof Ploeg) {
                    return $a->{$fallBackSort}() < $b->{$fallBackSort}() ? -1 : 1;
                } else {
                    return $a[$fallBackSort] < $b[$fallBackSort] ? -1 : 1;
                }
            }
            return ($aPoints < $bPoints) ? 1 : -1;
        });
    }

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
        $qb = $this->_em->getRepository('CyclearGameBundle:Ploeg')->createQueryBuilder('p');
        $subQ = $this->_em->getRepository('CyclearGameBundle:Uitslag')->createQueryBuilder('u')
            ->innerJoin('u.wedstrijd', 'w')
            ->where($qb->expr()->between('w.datum', ':start', ':end'))->andWhere('u.ploeg = p')
            ->select('IFNULL(SUM(u.ploegPunten),0)');
        $qb->select('p')
            ->where('p.seizoen = :seizoen')
            ->addSelect(sprintf('(%s) AS punten', $subQ->getDQL()))
            ->groupBy('p')
            ->orderBy('punten', 'DESC')
            ->addOrderBy('p.afkorting', 'ASC');
        $qb->setParameters(['start' => $start, 'end' => $end, 'seizoen' => $seizoen]);
        // flatten the result
        return array_map(function ($row) {
            return array_merge($row[0], ['punten' => $row['punten']]);
        }, $qb->getQuery()->getArrayResult());
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

    public function getPuntenByPloegForDraftTransfers($seizoen = null, Ploeg $ploeg = null)
    {
        if (null === $seizoen) {
            $seizoen = $this->_em->getRepository("CyclearGameBundle:Seizoen")->getCurrent();
        }
        $subQ = $this->_em->getRepository('CyclearGameBundle:Renner')->createQueryBuilder('r');
        $subQ->innerJoin('Cyclear\GameBundle\Entity\Transfer', 't', 'WITH',
            't.renner = r AND t.transferType = :draft AND t.seizoen = :seizoen')->andWhere('t.ploegNaar = :p');
        $subQ->select('DISTINCT r.id');
        $subQPoints = $this->_em->getRepository('CyclearGameBundle:Uitslag')->createQueryBuilder('u');
        $subQPoints->select('IFNULL(SUM(u.rennerPunten),0)')
            ->innerJoin('u.wedstrijd', 'w')
            ->where('w.seizoen = :seizoen')
            ->andWhere($subQ->expr()->in('u.renner', $subQ->getDQL()));
        $subQPoints->setParameter('draft', Transfer::DRAFTTRANSFER)->setParameter('seizoen', $seizoen);
        if ($ploeg) {
            $retPloeg = $this->_em->getRepository('CyclearGameBundle:Ploeg')
                ->createQueryBuilder('p')->where($subQ->expr()->eq('p', $ploeg->getId()))->getQuery()->getArrayResult();
            $retPloeg['punten'] = $subQPoints->setParameter('p', $ploeg)->getQuery()->getSingleScalarResult();
            return [$retPloeg];
        }
        $res = [];
        foreach ($this->_em->getRepository('CyclearGameBundle:Ploeg')
                     ->createQueryBuilder('p')->where('p.seizoen = :seizoen')
                     ->setParameter('seizoen', $seizoen)->getQuery()->getArrayResult() as $ploeg) {
            $subQPoints->setParameter('p', $ploeg['id']);
            $ploeg['punten'] = $subQPoints->getQuery()->getSingleScalarResult();
            $res[] = $ploeg;
        }
        static::puntenSort($res, 'afkorting');
        return $res;
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
        $res = [];
        foreach ($this->_em->getRepository('CyclearGameBundle:Ploeg')
                     ->createQueryBuilder('p')->where('p.seizoen = :seizoen')
                     ->setParameter('seizoen', $seizoen)->getQuery()->getResult() as $ploeg) {

            $teamResults = $this->getUitslagenForPloegForLostDraftsQb($ploeg, $seizoen);
            $teamPoints = 0;
            /** @var Uitslag $teamResult */
            foreach ($teamResults->getQuery()->getResult() as $teamResult) {
                $teamPoints += $teamResult->getRennerPunten();
            }
            $ploeg->setPunten($teamPoints);
            $res[] = $ploeg;
        }
        static::puntenSort($res);
        return $res;
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
        $sql = sprintf("SELECT p.id AS id, p.naam AS naam, p.afkorting AS afkorting, 400 as d,
                ((SELECT IFNULL(SUM(u.ploegPunten),0)
                FROM Uitslag u
                INNER JOIN Wedstrijd w ON u.wedstrijd_id = w.id
                WHERE w.seizoen_id = :seizoen_id AND u.ploeg_id = p.id AND u.renner_id IN (%s))

                -

                (SELECT IFNULL(SUM(u.rennerPunten),0)
                FROM Uitslag u
                INNER JOIN Wedstrijd w ON u.wedstrijd_id = w.id AND w.seizoen_id = :seizoen_id
                INNER JOIN draftriders dr ON u.renner_id = dr.renner_id
                WHERE dr.ploeg_id = p.id AND (u.ploeg_id IS NULL OR u.ploeg_id <> p.id OR u.ploeg_id = p.id AND u.ploegPunten = 0))

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
            ->andWhere('(u.ploeg != :ploeg OR u.ploeg IS NULL) OR (u.ploeg = :ploeg AND u.ploegPunten = 0)')
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
