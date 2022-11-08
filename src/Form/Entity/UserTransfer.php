<?php declare(strict_types=1);

namespace App\Form\Entity;

use App\Entity\Seizoen;
use App\Validator\Constraints as CyclearAssert;

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
    public function setDatum($datum): void
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

    public function setRennerIn(\App\Entity\Renner $renner): void
    {
        $this->renner_in = $renner;
    }

    public function setPloeg(\App\Entity\Ploeg $ploeg): void
    {
        $this->ploeg = $ploeg;
    }

    public function getPloeg()
    {
        return $this->ploeg;
    }

    /**
     * @return Seizoen
     */
    public function getSeizoen()
    {
        return $this->seizoen;
    }

    public function setSeizoen(Seizoen $seizoen): void
    {
        $this->seizoen = $seizoen;
    }

    public function getRennerUit()
    {
        return $this->renner_uit;
    }

    public function setRennerUit(\App\Entity\Renner $renner_uit): void
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
    public function setUserComment($userComment): void
    {
        $this->userComment = $userComment;
    }
}
