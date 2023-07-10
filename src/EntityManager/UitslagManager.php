<?php declare(strict_types=1);

namespace App\EntityManager;

use App\Calculator\PointsCalculator;
use App\CQRanking\Nationality\NationalityResolver;
use App\CQRanking\Parser\CQParser;
use App\CQRanking\Parser\Twitter\TwitterParser;
use App\Entity\Country;
use App\Entity\Renner;
use App\Entity\Seizoen;
use App\Entity\UitslagType;
use App\Entity\Wedstrijd;
use App\Repository\RennerRepository;
use App\Repository\TransferRepository;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Translatable\Entity\Translation;
use Symfony\Component\DomCrawler\Crawler;

class UitslagManager
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly CQParser $cqParser,
        private readonly PointsCalculator $puntenCalculator,
        private readonly RennerManager $rennerManager,
        private readonly TwitterParser $twitterParser,
        private readonly RennerRepository $rennerRepository,
        private readonly TransferRepository $transferRepository,
    ) {
    }

    public function prepareUitslagen(UitslagType $uitslagType, Crawler $crawler, Wedstrijd $wedstrijd, Seizoen $seizoen, \DateTime $puntenReferentieDatum = null): array
    {
        $parseStrategy = $uitslagType->getCqParsingStrategy();
        $uitslagregels = $this->cqParser->getResultRows($crawler, $parseStrategy);
        $rows = 0;
        $maxResults = $uitslagType->getMaxResults();
        $uitslagen = [];
        $rennerManager = $this->rennerManager;
        foreach ($uitslagregels as $uitslagregel) {
            if (0 === strcmp(strtolower($uitslagregel['pos']), 'leader')) {
                continue;
            }
            $row = [];
            $row['ploegPunten'] = 0;
            $row['positie'] = $uitslagregel['pos'];
            $row['rennerPunten'] = $uitslagregel['points'];
            $row['ploeg'] = null;
            $renner = $this->rennerRepository->findOneByCQId($uitslagregel['cqranking_id']);
            if (null !== $renner) {
                $row['renner'] = $rennerManager->getRennerSelectorTypeStringFromRenner($renner);
                $transfer = $this->transferRepository->findLastTransferForDate($renner, $wedstrijd->getDatum(), $seizoen);
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

    private function handleUnknownRenner($rennerString, $nat): void
    {
        $renner = $this->rennerManager->createRennerFromRennerSelectorTypeString($rennerString);
        $countryFullName = NationalityResolver::getFullNameFromCode($nat);
        $transRepo = $this->entityManager->getRepository(Translation::class);
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
        $this->entityManager->flush();
    }
}
