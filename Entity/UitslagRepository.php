<?php

namespace Cyclear\GameBundle\Entity;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityRepository;
use Cyclear\GameBundle\Entity\Renner;
use Cyclear\GameBundle\Entity\Ploeg;

class UitslagRepository extends EntityRepository {

    public function getPuntenByPloeg() {
        // FIXME RIGHT JOIN van maken om alle ploegen te listen
        $qb = $this->getEntityManager()->createQuery("
                SELECT p.id, p.naam, SUM(u.ploegPunten) AS punten 
                FROM CyclearGameBundle:Uitslag u
                LEFT JOIN u.ploeg p
                WHERE u.ploeg IS NOT NULL
                GROUP BY u.ploeg
                ORDER BY punten DESC");
        return $qb->getResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);
    }

    public function getPuntenByPloegForPeriode(Periode $periode) {
        // FIXME RIGHT JOIN van maken om alle ploegen te listen
        $start = clone $periode->getStart();
        $start->setTime('00', '00', '00');
        $end = clone $periode->getEind();
        $end->setTime('23', '59', '59');

        $qb = $this->getEntityManager()->createQuery("
                SELECT p.id, p.naam, SUM(u.ploegPunten) AS punten 
                FROM CyclearGameBundle:Uitslag u
                JOIN u.ploeg p
                WHERE u.ploeg IS NOT NULL AND u.datum BETWEEN :start AND :end
                GROUP BY u.ploeg
                ORDER BY punten DESC")->setParameters(array('start' => $start, 'end' => $end));
        return $qb->getResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);
    }

    public function getCountForPosition($pos = 1) {

        $sql = "SELECT u.ploeg_id AS id, p.naam AS naam, SUM(IF(positie = :pos,1,0)) AS freqByPos
                FROM Uitslag u 
                RIGHT JOIN Ploeg p ON u.ploeg_id = p.id
                GROUP BY p.id
                ORDER BY freqByPos DESC, p.naam ASC
                ";
        $conn = $this->getEntityManager()->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":pos", $pos);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_NAMED);
    }

}
