<?php

namespace Cyclear\GameBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Cyclear\GameBundle\Entity\Ploeg;

class PloegRepository extends EntityRepository {

    public function getRennersWithPunten($ploeg){
        
        $sql = "SELECT u.ploeg_id AS id, r.id AS rennerId, r.naam, 
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
        $stmt->bindParam(":ploeg", $ploegId );
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_NAMED);
    }
    
}