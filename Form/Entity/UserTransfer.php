<?php
namespace Cyclear\GameBundle\Form\Entity;

use Cyclear\GameBundle\Validator\Constraints as CyclearAssert;

/**
 * @CyclearAssert\UserTransfer
 */
class UserTransfer {
    
    private $renner_in;
    
    public function getRennerIn(){
        return $this->renner_in;
    }
    
    public function setRennerIn($renner){
        $this->renner_in = $renner;
    }
    
    public function setPloeg($ploeg){
        $this->ploeg = $ploeg;
    }
    
    public function getPloeg(){
        return $this->ploeg;
    }
    
}
