<?php

namespace Cyclear\GameBundle\EntityManager;

use Doctrine\ORM\EntityManager;

class RennerManager {
    
    /**
     *
     * @param EntityManager $registry 
     */
    public function __construct(){
    }
    
    /**
     *
     * @param type $rennerString
     * @return \Cyclear\GameBundle\Entity\Renner 
     */
    public function createRennerFromRennerSelectorTypeString( $rennerString ){
        $rennerString = trim($rennerString);
        $firstBracket = strpos($rennerString,'[');
        $lastBracket = strpos( $rennerString, ']' );
        $cqId = trim( substr( $rennerString, 0, $firstBracket ) );
        $name = substr($rennerString, $firstBracket + 1, $lastBracket - $firstBracket - 1);
        $renner = new \Cyclear\GameBundle\Entity\Renner();
        $renner->setNaam($name);
        $renner->setCqRanking_id($cqId);
        return $renner;
    }
    
}



?>
