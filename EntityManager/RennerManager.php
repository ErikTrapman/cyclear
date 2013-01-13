<?php

namespace Cyclear\GameBundle\EntityManager;

use Cyclear\GameBundle\Entity\Renner;

class RennerManager
{

    public function __construct()
    {
        
    }

    /**
     *
     * @param type $rennerString
     * @return Renner 
     */
    public function createRennerFromRennerSelectorTypeString($rennerString)
    {
        $cqId = $this->getCqIdFromRennerSelectorTypeString($rennerString);
        $renner = new Renner();
        $renner->setNaam( $this->getNameFromRennerSelectorTypeString($rennerString, $cqId) );
        $renner->setCqRanking_id($cqId);
        return $renner;
    }

    public function getCqIdFromRennerSelectorTypeString($string)
    {
        sscanf($string, "[%d]", $cqId);
        return $cqId;
    }

    public function getNameFromRennerSelectorTypeString($string, $cqId = null)
    {
        if(null === $cqId){
            $cqId = $this->getCqIdFromRennerSelectorTypeString($string);
        }
        return trim(str_replace(sprintf('[%d]', $cqId), '', $string));
    }

    public function getRennerSelectorTypeString($cqRankingId, $name)
    {
        return sprintf("[%d] %s", $cqRankingId, $name);
    }
}