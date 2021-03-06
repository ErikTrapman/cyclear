<?php

/*
 * This file is part of the Cyclear-game package.
 *
 * (c) Erik Trapman <veggatron@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Form\Validator\Constraints as CyclearAssert;

/**
 * App\Entity\Transfer
 *
 * @ORM\Table(name="Transfer")
 * @ORM\Entity(repositoryClass="App\Repository\TransferRepository")
 */
class Transfer
{
    const DRAFTTRANSFER = 32,
        ADMINTRANSFER = 64,
        USERTRANSFER = 128;

    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var Renner $renner
     *
     * @ORM\JoinColumn(name="renner_id")
     * @ORM\ManyToOne(targetEntity="App\Entity\Renner",inversedBy="transfers")
     */
    private $renner;

    /**
     * @var Ploeg $ploegVan
     *
     * @ORM\JoinColumn(name="ploegvan_id", nullable=true)
     * @ORM\ManyToOne(targetEntity="App\Entity\Ploeg")
     */
    private $ploegVan;

    /**
     * @var Ploeg $ploegNaar
     *
     * @ORM\JoinColumn(name="ploegnaar_id", nullable=true)
     * @ORM\ManyToOne(targetEntity="App\Entity\Ploeg")
     */
    private $ploegNaar;

    /**
     * @var \DateTime $datum
     *
     * @ORM\Column(name="datum", type="datetime")
     */
    private $datum;

    /**
     *
     * @ORM\Column(type="integer", name="transferType")
     */
    private $transferType;

    /**
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Seizoen")
     */
    private $seizoen;

    /**
     *
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE", name="inversionTransfer_id")
     * @ORM\OneToOne(targetEntity="App\Entity\Transfer", cascade={"all"})
     */
    private $inversionTransfer;

    /**
     * @ORM\Column(nullable=true, name="userComment")
     */
    private $userComment;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set renner
     *
     * @param object $renner
     */
    public function setRenner($renner)
    {
        $this->renner = $renner;
    }

    /**
     * Get renner
     *
     * @return object
     */
    public function getRenner()
    {
        return $this->renner;
    }

    /**
     * Set ploegVan
     *
     * @param object $ploegVan
     */
    public function setPloegVan($ploegVan)
    {
        $this->ploegVan = $ploegVan;
    }

    /**
     * Get ploegVan
     *
     * @return object
     */
    public function getPloegVan()
    {
        return $this->ploegVan;
    }

    /**
     * Set ploegNaar
     *
     * @param object $ploegNaar
     */
    public function setPloegNaar($ploegNaar)
    {
        $this->ploegNaar = $ploegNaar;
    }

    /**
     * Get ploegNaar
     *
     * @return object
     */
    public function getPloegNaar()
    {
        return $this->ploegNaar;
    }

    /**
     * Set datum
     *
     * @param \DateTime $datum
     */
    public function setDatum($datum)
    {
        $this->datum = $datum;
    }

    /**
     * Get datum
     *
     * @return \DateTime
     */
    public function getDatum()
    {
        return $this->datum;
    }

    public function getTransferType()
    {
        return $this->transferType;
    }

    public function getTransferTypeFormatted()
    {
        switch ($this->transferType) {
            case self::ADMINTRANSFER:
                return "admin-transfer";
            case self::DRAFTTRANSFER:
                return "draft";
            case self::USERTRANSFER:
                return "gebruiker";
        }
    }

    public function setTransferType($transferType)
    {
        $this->transferType = $transferType;
    }

    /**
     * @return Seizoen
     */
    public function getSeizoen()
    {
        return $this->seizoen;
    }

    public function setSeizoen($seizoen)
    {
        $this->seizoen = $seizoen;
    }

    public function getInversionTransfer()
    {
        return $this->inversionTransfer;
    }

    public function setInversionTransfer($inversionTransfer)
    {
        $this->inversionTransfer = $inversionTransfer;
    }

    public function __toString()
    {
        return (string)$this->getId();
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