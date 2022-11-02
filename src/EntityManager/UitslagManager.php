<?php declare(strict_types=1);

namespace App\EntityManager;

use App\Calculator\PuntenCalculator;
use App\CQRanking\Nationality\NationalityResolver;
use App\CQRanking\Parser\CQParser;
use App\CQRanking\Parser\Twitter\TwitterParser;
use App\Entity\Country;
use App\Entity\Renner;
use App\Entity\Seizoen;
use App\Entity\Transfer;
use App\Entity\UitslagType;
use App\Entity\Wedstrijd;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;

class UitslagManager
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var PuntenCalculator
     */
    private $puntenCalculator;

    /**
     * @var CQParser
     */
    private $cqParser;

    /**
     * @var string
     */
    private $cqRankingWedstrijdUrl;

    /**
     * @var RennerManager
     */
    private $rennerManager;

    /**
     * @var TwitterParser
     */
    private $twitterParser;

    /**
     * UitslagManager constructor.
     * @param EntityManager $em
     * @param $cqRankingWedstrijdUrl
     */
    public function __construct(
        EntityManagerInterface $em,
        CQParser $parser,
        PuntenCalculator $puntenCalculator,
        $cqRankingWedstrijdUrl,
        RennerManager $rennerManager,
        TwitterParser $twitterParser
    ) {
        $this->entityManager = $em;
        $this->puntenCalculator = $puntenCalculator;
        $this->cqParser = $parser;
        $this->cqRankingWedstrijdUrl = $cqRankingWedstrijdUrl;
        $this->rennerManager = $rennerManager;
        $this->twitterParser = $twitterParser;
    }

    /**
     * @param $crawler
     * @param Seizoen $seizoen
     * @param null $puntenReferentieDatum
     * @return array
     */
    public function prepareUitslagen(UitslagType $uitslagType, $crawler, Wedstrijd $wedstrijd, $seizoen, $puntenReferentieDatum = null)
    {
        $parseStrategy = $uitslagType->getCqParsingStrategy();
        $uitslagregels = $this->cqParser->getResultRows($crawler, $parseStrategy);
        $rows = 0;
        $maxResults = $uitslagType->getMaxResults();
        $uitslagen = [];
        $rennerRepo = $this->entityManager->getRepository(Renner::class);
        $transferRepo = $this->entityManager->getRepository(Transfer::class);
        $rennerManager = $this->rennerManager;
        foreach ($uitslagregels as $uitslagregel) {
            if (strcmp(strtolower($uitslagregel['pos']), 'leader') === 0) {
                continue;
            }
            $row = [];
            $row['ploegPunten'] = 0;
            $row['positie'] = $uitslagregel['pos'];
            $row['rennerPunten'] = $uitslagregel['points'];
            $row['ploeg'] = null;
            $renner = $rennerRepo->findOneByCQId($uitslagregel['cqranking_id']);
            if (null !== $renner) {
                $row['renner'] = $rennerManager->getRennerSelectorTypeStringFromRenner($renner);
                $transfer = $transferRepo->findLastTransferForDate($renner, $wedstrijd->getDatum(), $seizoen);
                if (null !== $transfer) {
                    $row['ploeg'] = (null !== $transfer->getPloegNaar()) ? $transfer->getPloegNaar()->getId() : null;
                    if (null !== $row['ploeg'] && $this->puntenCalculator->canGetTeamPoints($renner, $wedstrijd->getDatum(), $seizoen, $puntenReferentieDatum)) {
                        $row['ploegPunten'] = $uitslagregel['points'];
                    }
                }
            } else {
                $rennerString = $rennerManager->getRennerSelectorTypeString($uitslagregel['cqranking_id'], $uitslagregel['name']);
                $this->handleUnknownRenner($rennerString, $uitslagregel['nat']);
                $row['renner'] = $rennerString;
            }
            $uitslagen[] = $row;
            ++$rows;
            if ($rows == $maxResults) {
                break;
            }
        }
        return $uitslagen;
    }

    private function handleUnknownRenner($rennerString, $nat)
    {
        $renner = $this->rennerManager->createRennerFromRennerSelectorTypeString($rennerString);
        $countryFullName = NationalityResolver::getFullNameFromCode($nat);
        $transRepo = $this->entityManager->getRepository('Gedmo\\Translatable\\Entity\\Translation');
        $trans = $transRepo->findOneBy(['content' => $countryFullName, 'locale' => 'en_GB']);
        $countryRepo = $this->entityManager->getRepository(Country::class);
        if (null === $trans) {
            $country = $countryRepo->findOneByName($countryFullName);
        } else {
            $country = $countryRepo->find($trans->getForeignKey());
        }
        $renner->setCountry($country);
        $renner->setTwitter($this->twitterParser->getTwitterHandle($renner->getCqRankingId()));
        // save renner immediately to database.
        $this->entityManager->persist($renner);
        $this->entityManager->flush($renner);
    }
}
