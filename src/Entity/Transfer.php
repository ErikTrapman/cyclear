<?php declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * App\Entity\Transfer
 *
 * @ORM\Table(name="transfer")
 * @ORM\Entity(repositoryClass="App\Repository\TransferRepository")
 */
class Transfer
{
    public const DRAFTTRANSFER = 32;
    public const ADMINTRANSFER = 64;
    public const USERTRANSFER = 128;

    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\JoinColumn(name="renner_id")
     * @ORM\ManyToOne(targetEntity="App\Entity\Renner",inversedBy="transfers", fetch="EAGER")
     */
    private $renner;

    /**
     * @ORM\JoinColumn(name="ploegvan_id", nullable=true)
     * @ORM\ManyToOne(targetEntity="App\Entity\Ploeg")
     */
    private $ploegVan;

    /**
     * @ORM\JoinColumn(name="ploegnaar_id", nullable=true)
     * @ORM\ManyToOne(targetEntity="App\Entity\Ploeg")
     */
    private $ploegNaar;

    /**
     * @ORM\Column(name="datum", type="datetime")
     */
    private $datum;

    /**
     * @ORM\Column(type="integer", name="transferType")
     */
    private $transferType;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Seizoen")
     */
    private $seizoen;

    /**
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE", name="inversionTransfer_id")
     * @ORM\OneToOne(targetEntity="App\Entity\Transfer", cascade={"all"})
     */
    private $inversionTransfer;

    /**
     * @ORM\Column(nullable=true, name="userComment")
     */
    private $userComment;

    public function getId()
    {
        return $this->id;
    }

    public function setRenner(Renner $renner): void
    {
        $this->renner = $renner;
    }

    public function getRenner()
    {
        return $this->renner;
    }

    public function setPloegVan(Ploeg|null $ploegVan): void
    {
        $this->ploegVan = $ploegVan;
    }

    public function getPloegVan()
    {
        return $this->ploegVan;
    }

    public function setPloegNaar(Ploeg|null $ploegNaar): void
    {
        $this->ploegNaar = $ploegNaar;
    }

    public function getPloegNaar()
    {
        return $this->ploegNaar;
    }

    public function setDatum(\DateTime $datum): void
    {
        $this->datum = $datum;
    }

    public function getDatum()
    {
        return $this->datum;
    }

    public function getTransferType()
    {
        return $this->transferType;
    }

    public function getTransferTypeFormatted(): string
    {
        switch ($this->transferType) {
            case self::ADMINTRANSFER:
                return 'admin-transfer';
            case self::DRAFTTRANSFER:
                return 'draft';
            case self::USERTRANSFER:
                return 'gebruiker';
        }
    }

    public function setTransferType(int $transferType): void
    {
        $this->transferType = $transferType;
    }

    public function getSeizoen()
    {
        return $this->seizoen;
    }

    public function setSeizoen($seizoen): void
    {
        $this->seizoen = $seizoen;
    }

    public function getInversionTransfer()
    {
        return $this->inversionTransfer;
    }

    public function setInversionTransfer(self $inversionTransfer): void
    {
        $this->inversionTransfer = $inversionTransfer;
    }

    public function __toString()
    {
        return (string)$this->getId();
    }

    public function getUserComment()
    {
        return $this->userComment;
    }

    public function setUserComment($userComment): void
    {
        $this->userComment = $userComment;
    }
}
