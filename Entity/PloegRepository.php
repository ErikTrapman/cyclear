<?php

namespace Cyclear\GameBundle\Entity;

use Doctrine\ORM\EntityRepository;

class PloegRepository extends EntityRepository
{

    public function getRenners($ploeg, $seizoen = null)
    {
        if (null === $seizoen) {
            $seizoen = $this->_em->getRepository("CyclearGameBundle:Seizoen")->getCurrent();
        }
        $renners = array();
        foreach ($this->_em->getRepository("CyclearGameBundle:Contract")
            ->createQueryBuilder('c')
            ->where('c.ploeg = :ploeg')
            ->andWhere('c.seizoen = :seizoen')
            ->andWhere('c.eind IS NULL')
            ->setParameters(array('ploeg' => $ploeg, 'seizoen' => $ploeg->getSeizoen()))
            ->getQuery()->getResult() as $contract) {

            $renners[] = $contract->getRenner();
        }
        return $renners;
    }
    
    public function getRennersWithPunten($ploeg)
    {
        $renners = $this->getRenners($ploeg);
        $rennerIds = array();
        foreach ($renners as $renner) {
            $rennerIds[] = $renner->getId();
        }
        $sql = sprintf("SELECT r.id AS id, r.naam, 
                    IFNULL(SUM(ploegPunten),0) AS ploegPunten, 
                    IFNULL(SUM(rennerPunten),0) AS rennerPunten
                FROM Uitslag u 
                RIGHT JOIN Renner r ON u.renner_id = r.id
                WHERE r.id IN ( %s )
                GROUP BY r.id
                ORDER BY ploegPunten DESC, r.naam ASC
                ", (!empty($renners)) ? implode(',', $rennerIds) : 0 );
        $conn = $this->getEntityManager()->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->execute(array(":ploeg" => $ploeg->getId(), ":seizoen" => $ploeg->getSeizoen()->getId()));
        return $stmt->fetchAll(\PDO::FETCH_NAMED);
    }

    
}