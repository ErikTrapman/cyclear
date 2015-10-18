<?php

/*
 * This file is part of the Cyclear-game package.
 *
 * (c) Erik Trapman <veggatron@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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

    private $datum;

    private $ploeg;

    private $userComment;

    /**
     * @param mixed $datum
     */
    public function setDatum($datum)
    {
        $this->datum = $datum;
    }

    /**
     * @return mixed
     */
    public function getDatum()
    {
        return $this->datum;
    }


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

    /**
     * @return mixed
     */
    public function getUserComment()
    {
        return $this->userComment;
    }

    /**
     * @param mixed $userComment
     */
    public function setUserComment($userComment)
    {
        $this->userComment = $userComment;
    }

}
