<?php

/*
 * This file is part of the Cyclear-game package.
 *
 * (c) Erik Trapman <veggatron@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cyclear\GameBundle\EntityManager;

use Cyclear\GameBundle\Calculator\PuntenCalculator;
use Cyclear\GameBundle\Entity\Uitslag;
use Cyclear\GameBundle\Entity\Wedstrijd;
use Cyclear\GameBundle\Entity\UitslagType;
use Doctrine\ORM\EntityManager;
use ErikTrapman\Bundle\CQRankingParserBundle\Nationality\NationalityResolver;
use ErikTrapman\Bundle\CQRankingParserBundle\Parser\CQParser;
use ErikTrapman\Bundle\CQRankingParserBundle\Parser\Twitter\TwitterParser;

class UitslagManager
{
    /**
     *
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

    private $cqRankingWedstrijdUrl;

    /**
     * @var RennerManager
     */
    private $rennerManager;

    /**
     * @var NationalityResolver
     */
    private $nationalityResolver;

    /**
     * @var TwitterParser
     */
    private $twitterParser;

    public function __construct(EntityManager $em,
                                CQParser $parser,
                                PuntenCalculator $puntenCalculator,
                                $cqRankingWedstrijdUrl,
                                RennerManager $rennerManager,
                                NationalityResolver $cqNationalityResolver,
                                TwitterParser $twitterParser)
    {
        $this->entityManager = $em;
        $this->puntenCalculator = $puntenCalculator;
        $this->cqParser = $parser;
        $this->cqRankingWedstrijdUrl = $cqRankingWedstrijdUrl;
        $this->rennerManager = $rennerManager;
        $this->nationalityResolver = $cqNationalityResolver;
        $this->twitterParser = $twitterParser;
    }

    /**
     *
     * @param UitslagType $uitslagType
     * @param type $crawler
     * @param Wedstrijd $wedstrijd
     * @param type $puntenReferentieDatum
     * @return Uitslag[]
     */
    public function prepareUitslagen(UitslagType $uitslagType, $crawler, Wedstrijd $wedstrijd, $seizoen, $puntenReferentieDatum = null)
    {
        $parseStrategy = $uitslagType->getCqParsingStrategy();
        $uitslagregels = $this->cqParser->getResultRows($crawler, $parseStrategy);
        $rows = 0;
        $maxResults = $uitslagType->getMaxResults();
        $uitslagen = array();
        $rennerRepo = $this->entityManager->getRepository('CyclearGameBundle:Renner');
        $transferRepo = $this->entityManager->getRepository('CyclearGameBundle:Transfer');
        $rennerManager = $this->rennerManager;
        foreach ($uitslagregels as $uitslagregel) {
            if (strcmp(strtolower($uitslagregel['pos']), 'leader') === 0) {
                continue;
            }
            $row = array();
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
            $rows++;
            if ($rows == $maxResults) {
                break;
            }
        }
        return $uitslagen;
    }

    private function handleUnknownRenner($rennerString, $nat)
    {
        $renner = $this->rennerManager->createRennerFromRennerSelectorTypeString($rennerString);
        $countryFullName = $this->nationalityResolver->getFullNameFromCode($nat);
        $transRepo = $this->entityManager->getRepository('Gedmo\\Translatable\\Entity\\Translation');
        $trans = $transRepo->findOneBy(array('content' => $countryFullName, 'locale' => 'en_GB'));
        $countryRepo = $this->entityManager->getRepository("CyclearGameBundle:Country");
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