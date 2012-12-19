<?php

namespace Cyclear\GameBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Cyclear\GameBundle\Entity\Ploeg;

class PloegRepository extends EntityRepository
{

    public function getRennersWithPunten($ploeg)
    {
        echo __METHOD__;
        die;
        
        
        $sql = "SELECT r.id AS id, r.naam, 
                    IFNULL(SUM(ploegPunten),0) AS ploegPunten, 
                    IFNULL(SUM(rennerPunten),0) AS rennerPunten
                FROM Uitslag u 
                RIGHT JOIN Renner r ON u.renner_id = r.id
                WHERE r.ploeg_id = :ploeg
                GROUP BY r.id
                ORDER BY ploegPunten DESC, r.naam ASC
                ";
        $conn = $this->getEntityManager()->getConnection();
        $stmt = $conn->prepare($sql);
        $ploegId = $ploeg->getId();
        $stmt->bindParam(":ploeg", $ploegId);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_NAMED);
    }

    public function getRennersForPloeg($ploeg, $seizoen = null)
    {
        if (null === $seizoen) {
            $seizoen = $this->_em->getRepository("CyclearGameBundle:Seizoen")->getCurrent();
        }
        $renners = array();
        foreach ($this->_em->getRepository("CyclearGameBundle:Contract")
            ->createQueryBuilder('c')
            ->where('c.ploeg = :ploeg')
            ->andWhere('c.seizoen = :seizoen')
            ->andWhere('c.eind IS NOT NULL')
            ->setParameters(array('ploeg' => $ploeg, 'seizoen' => $seizoen))
            ->getQuery()->getResult() as $contract) {

            $renners[] = $contract->getRenner();
        }
        return $renners;
    }
}