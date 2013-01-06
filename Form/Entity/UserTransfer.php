<?php

namespace Cyclear\GameBundle\Form\Entity;

use Cyclear\GameBundle\Validator\Constraints as CyclearAssert;

/**
 * @CyclearAssert\UserTransfer
 */
class UserTransfer
{
    private $renner_in;
    
    private $renner_uit;
    
    private $seizoen;

    public function getRennerIn()
    {
        return $this->renner_in;
    }

    public function setRennerIn($renner)
    {
        $this->renner_in = $renner;
    }

    public function setPloeg($ploeg)
    {
        $this->ploeg = $ploeg;
    }

    public function getPloeg()
    {
        return $this->ploeg;
    }
    
    public function getSeizoen()
    {
        return $this->seizoen;
    }

    public function setSeizoen($seizoen)
    {
        $this->seizoen = $seizoen;
    }

    
    public function getRennerUit()
    {
        return $this->renner_uit;
    }

    public function setRennerUit($renner_uit)
    {
        $this->renner_uit = $renner_uit;
    }



    
}
