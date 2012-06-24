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
        $lastTransfer = $this->em->getRepository('CyclearGameBundle:Transfer')->findLastTransferForDate($renner, $referentieDatum);
        if (!$lastTransfer) {
            return false;
        }
        $transferDatum = clone $lastTransfer->getDatum();
        $transferDatum->setTime("00","00","00");
        if ($transferDatum >= $referentieDatum) {
            return false;
        }
        return true;
    }

    public function getTotalPointsForPloeg($ploeg) {
        // optelling alleen voor huidige jaar.
    }

    public function getTotalPointsForRenner($renner) {
        // optelling alleen voor huidige jaar
    }

}

?>
