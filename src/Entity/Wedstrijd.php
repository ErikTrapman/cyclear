<?php declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * App\Entity\Wedstrijd
 *
 * @ORM\Table(name="wedstrijd")
 * @ORM\Entity(repositoryClass="App\Repository\WedstrijdRepository")
 */
class Wedstrijd
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="datum", type="datetime")
     */
    private $datum;

    /**
     * @var string
     *
     * @ORM\Column(name="naam", type="string", length=255)
     */
    private $naam;

    /**
     * @var Uitslag[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Uitslag", mappedBy="wedstrijd", cascade={"all"})
     * @ORM\OrderBy({"positie" = "ASC"})
     */
    private $uitslagen;

    /**
     * @var Seizoen
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Seizoen")
     */
    private $seizoen;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", name="generalClassification")
     */
    private $generalClassification = false;

    /**
     * @var string
     *
     * @ORM\Column(nullable=true, name="externalIdentifier")
     */
    private $externalIdentifier;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", nullable=true, name="fullyProcessed")
     */
    private $fullyProcessed;

    public function __construct()
    {
        $this->uitslagen = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set datum
     *
     * @param \DateTime $datum
     */
    public function setDatum($datum): void
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

    /**
     * Set naam
     *
     * @param string $naam
     */
    public function setNaam($naam): void
    {
        $this->naam = $naam;
    }

    /**
     * Get naam
     *
     * @return string
     */
    public function getNaam()
    {
        return $this->naam;
    }

    /**
     * @return Uitslag[]|ArrayCollection
     */
    public function getUitslagen()
    {
        return $this->uitslagen;
    }

    public function getSeizoen(): Seizoen
    {
        return $this->seizoen;
    }

    public function setSeizoen(Seizoen $seizoen): void
    {
        $this->seizoen = $seizoen;
    }

    /**
     * @return mixed
     */
    public function isGeneralClassification()
    {
        return $this->generalClassification;
    }

    /**
     * @param mixed $generalClassification
     */
    public function setGeneralClassification($generalClassification): void
    {
        $this->generalClassification = $generalClassification;
    }

    /**
     * @return mixed
     */
    public function getExternalIdentifier()
    {
        return $this->externalIdentifier;
    }

    /**
     * @param mixed $externalIdentifier
     */
    public function setExternalIdentifier($externalIdentifier): void
    {
        $this->externalIdentifier = $externalIdentifier;
    }

    /**
     * @return mixed
     */
    public function getFullyProcessed()
    {
        return $this->fullyProcessed;
    }

    /**
     * @param mixed $fullyProcessed
     */
    public function setFullyProcessed($fullyProcessed): void
    {
        $this->fullyProcessed = $fullyProcessed;
    }

    public function addUitslag(Uitslag $uitslag): void
    {
        if (!$this->uitslagen->contains($uitslag)) {
            $this->uitslagen->add($uitslag);
        }
    }

    /**
     * Fetch the Uitslagen, grouped and totalled per team.
     *
     * @param mixed $keepRiders
     *
     * @return ((Uitslag|int|mixed)[][]|int|mixed)[][]
     *
     * @psalm-return array<array{total: 0|mixed, hits: 0|positive-int, ploeg: mixed, renners: array<array{renner: mixed, hits: int, total: 0|mixed, result?: Uitslag}>}>
     */
    public function getUitslagenGrouped($keepRiders = false): array
    {
        $group = [];
        foreach ($this->uitslagen as $uitslag) {
            $ploeg = $uitslag->getPloeg();
            if ($ploeg) {
                if (!array_key_exists($ploeg->getId(), $group)) {
                    $group[$ploeg->getId()] = [];
                }
                $group[$ploeg->getId()][] = $uitslag;
            }
        }
        $ret = [];
        foreach ($group as $ploegId => $uitslagen) {
            $reduce = array_reduce($uitslagen, function ($init, Uitslag $uitslag) {
                return $init + $uitslag->getPloegPunten();
            }, 0);
            $ret[$ploegId] = [
                'total' => $reduce,
                'hits' => count($uitslagen),
                'ploeg' => $uitslagen[0]->getPloeg(),
                'renners' => [],
            ];
            if ($keepRiders) {
                foreach ($uitslagen as $uitslag) {
                    $renner = $uitslag->getRenner();
                    $index = $renner->getId();
                    if (!array_key_exists($index, $ret[$ploegId]['renners'])) {
                        $ret[$ploegId]['renners'][$index] = ['renner' => $renner, 'hits' => 0, 'total' => 0];
                    }
                    ++$ret[$ploegId]['renners'][$index]['hits'];
                    $ret[$ploegId]['renners'][$index]['total'] += $uitslag->getPloegPunten();
                    $ret[$ploegId]['renners'][$index]['result'] = $uitslag;
                }
            }
        }
        uasort($ret, function ($a, $b) {
            if ($a['total'] == $b['total']) {
                return 0;
            }
            return $a['total'] < $b['total'] ? 1 : -1;
        });
        return $ret;
    }

    public function __toString()
    {
        return $this->getNaam();
    }
}
