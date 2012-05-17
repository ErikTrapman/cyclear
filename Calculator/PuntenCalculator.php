<?php
namespace Cyclear\GameBundle\Calculator;
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PuntenCalculator
 *
 * @author Erik
 */
class PuntenCalculator {
    
    
    private $em;
    
    public function __construct($em) {
        $this->em = $em;
    }

    public function canGetPoints($renner, $referentieDatum) {
        $lastTransfer = $this->em->getRepository('CyclearGameBundle:Renner')->findOneByJoinedByLastTransferOnOrBeforeDate(  $renner->getId(), $referentieDatum );
        if( ! $lastTransfer  ){
            return false;
        }
        $transferDatum =  new \DateTime( $lastTransfer[0]->getDatum()->format("Y-m-d") );
        
        //echo 'refdatum: '. $referentieDatum->format('Y-m-d').'<br>';
        
        //echo 'transferdatum: ' . $transferDatum->format('Y-m-d').'<br>';
        $transferDatum->sub(new \DateInterval('P1D'));
        //echo 'transferdatum - 1 dag: ' . $transferDatum->format('Y-m-d').'<br>';
        //die();
        if($transferDatum >= $referentieDatum){
            return false;
        }
        return true;
    }
    
    public function getTotalPointsForPloeg($ploeg){
        // optelling alleen voor huidige jaar.
    }
    
    public function getTotalPointsForRenner($renner){
        // optelling alleen voor huidige jaar
    }
    
}

?>
