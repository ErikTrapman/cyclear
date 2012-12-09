<?php

namespace Cyclear\GameBundle\Entity;

use Doctrine\ORM\EntityRepository;

class UitslagRepository extends EntityRepository
{

    public function getPuntenByPloeg($seizoen = null)
    {
        if (null === $seizoen) {
            $seizoen = $this->_em->getRepository("CyclearGameBundle:Seizoen")->getCurrent();
        }
        $sql = "SELECT p.id AS id, p.naam AS naam, 
                ( SELECT IFNULL(SUM(u.ploegPunten),0) FROM uitslag u WHERE u.seizoen_id = :seizoen_id AND u.ploeg_id = p.id ) AS punten 
                FROM ploeg p WHERE p.seizoen_id = :seizoen_id ORDER 
                BY punten DESC
                ";
        $conn = $this->getEntityManager()->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->execute(array(":seizoen_id" => $seizoen->getId()));
        return $stmt->fetchAll(\PDO::FETCH_NAMED);
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

        $sql = "SELECT *,
                    ( SELECT IFNULL(SUM(u.ploegPunten),0)
                    FROM uitslag u 
                    WHERE u.datum BETWEEN :start AND :end AND u.ploeg_id = p.id
                     ) AS punten
                FROM ploeg p WHERE p.seizoen_id = :seizoen_id
                GROUP BY p.id
                ORDER BY punten DESC, p.naam ASC
                ";
        $conn = $this->getEntityManager()->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->execute(array(":seizoen_id" => $seizoen->getId(), ":start" => $start->format('Y-m-d'), ":end" => $end->format('Y-m-d')));
        $res = $stmt->fetchAll(\PDO::FETCH_NAMED);
        return $res;
    }

    public function getCountForPosition($seizoen = null, $pos = 1)
    {
        if (null === $seizoen) {
            $seizoen = $this->_em->getRepository("CyclearGameBundle:Seizoen")->getCurrent();
        }

        $sql = "SELECT *,
                    IFNULL(( SELECT SUM(IF(u.positie = :pos,1,0)) AS freqByPos
                    FROM uitslag u 
                    WHERE u.ploeg_id = p.id AND u.seizoen_id = :seizoen_id
                     ),0) AS freqByPos
                FROM ploeg p WHERE p.seizoen_id = :seizoen_id
                GROUP BY p.id
                ORDER BY freqByPos DESC, p.naam ASC";
        $conn = $this->getEntityManager()->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->execute(array(":pos" => $pos, ":seizoen_id" => $seizoen->getId()));
        return $stmt->fetchAll(\PDO::FETCH_NAMED);
    }

    public function getPuntenForRenner($renner, $seizoen = null)
    {
        if (null === $seizoen) {
            $seizoen = $this->_em->getRepository("CyclearGameBundle:Seizoen")->getCurrent();
        }
        $qb = $this->createQueryBuilder("u")
            ->where("u.seizoen = :seizoen")
            ->andWhere("u.renner = :renner")
            ->setParameters(array(":seizoen" => $seizoen, ":renner" => $renner))
            ->orderBy("u.datum", "DESC")
        ;
        return $qb->getQuery()->getResult();
    }
}
