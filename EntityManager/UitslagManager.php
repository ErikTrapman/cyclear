<?php

namespace Cyclear\GameBundle\EntityManager;

use Cyclear\GameBundle\Calculator\PuntenCalculator;
use Cyclear\GameBundle\Entity\Uitslag;
use Cyclear\GameBundle\Entity\Wedstrijd;
use Cyclear\GameBundle\Entity\UitslagType;
use Doctrine\ORM\EntityManager;

class UitslagManager
{
    /**
     * 
     * @var EntityManager
     */
    private $entityManager;

    private $puntenCalculator;

    private $cqParser;

    private $cqRankingWedstrijdUrl;

    private $rennerManager;

    private $nationalityResolver;

    public function __construct(EntityManager $em, $parser, PuntenCalculator $puntenCalculator, $cqRankingWedstrijdUrl, $rennerManager, $cqNationalityResolver)
    {
        $this->entityManager = $em;
        $this->puntenCalculator = $puntenCalculator;
        $this->cqParser = $parser;
        $this->cqRankingWedstrijdUrl = $cqRankingWedstrijdUrl;
        $this->rennerManager = $rennerManager;
        $this->nationalityResolver = $cqNationalityResolver;
    }

    /**
     * 
     * @param UitslagType $uitslagType
     * @param type $crawler
     * @param Wedstrijd $wedstrijd
     * @param type $puntenReferentieDatum
     * @return array of Cyclear\GameBundle\Entity\Uitslag
     */
    public function prepareUitslagen(UitslagType $uitslagType, $crawler, Wedstrijd $wedstrijd, $puntenReferentieDatum = null)
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
                $transfer = $transferRepo->findLastTransferForDate($renner, $wedstrijd->getDatum());
                if (null !== $transfer) {
                    $row['ploeg'] = ( null !== $transfer->getPloegNaar() ) ? $transfer->getPloegNaar()->getId() : null;
                    if (null !== $row['ploeg'] && $this->puntenCalculator->canGetPoints($renner, $wedstrijd->getDatum(), $puntenReferentieDatum)) {
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
        // save renner immediately to database.
        $this->entityManager->persist($renner);
        $this->entityManager->flush($renner);
    }
}