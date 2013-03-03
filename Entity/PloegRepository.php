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

    public function getRennersWithPunten($ploeg, $seizoen = null)
    {
        if (null === $seizoen) {
            $seizoen = $this->_em->getRepository("CyclearGameBundle:Seizoen")->getCurrent();
        }
        $renners = $this->getRenners($ploeg);
        $ret = array();
        $uitslagRepo = $this->_em->getRepository("CyclearGameBundle:Uitslag");
        foreach ($renners as $renner) {
            $punten = $uitslagRepo->getPuntenForRennerWithPloeg($renner, $ploeg, $seizoen);
            $ret[] = array(0 => $renner, 'punten' => (int) $punten);
        }
        uasort($ret, function($a, $b) {
                if ($a['punten'] == $b['punten']) {
                    return 0;
                }
                return ($a['punten'] < $b['punten']) ? 1 : -1;
            });
        return $ret;
    }
}