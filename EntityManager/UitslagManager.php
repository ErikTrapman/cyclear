<?php

namespace Cyclear\GameBundle\EntityManager;

use Cyclear\GameBundle\Calculator\PuntenCalculator;
use Cyclear\GameBundle\Entity\Renner;
use Cyclear\GameBundle\Entity\Uitslag;
use Cyclear\GameBundle\Form\UitslagConfirmType;
use Cyclear\GameBundle\Form\UitslagNewType;
use Cyclear\GameBundle\Form\UitslagType;
use Cyclear\GameBundle\Parser\CQParser;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\DoctrineBundle\Registry;
use Symfony\Bundle\SecurityBundle\Tests\Functional\WebTestCase;
use Symfony\Component\CssSelector\CssSelector;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Form\Form;

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

    public function __construct(EntityManager $em, $parser, PuntenCalculator $puntenCalculator, $cqRankingWedstrijdUrl = '')
    {
        $this->entityManager = $em;
        $this->puntenCalculator = $puntenCalculator;
        $this->cqParser = $parser;
        $this->cqRankingWedstrijdUrl = $cqRankingWedstrijdUrl;
    }

    public function prepareUitslagenTwee($uitslagType, $crawler, $wedstrijd, $puntenReferentieDatum = null)
    {
        $parseStrategy = $uitslagType->getCqParsingStrategy();
        $uitslagregels = $this->cqParser->getResultRows($crawler, $parseStrategy);
        $rows = 0;
        $maxResults = $uitslagType->getMaxResults();
        $uitslagen = array();
        $rennerRepo = $this->entityManager->getRepository('CyclearGameBundle:Renner');
        $transferRepo = $this->entityManager->getRepository('CyclearGameBundle:Transfer');
        $rennerManager = new RennerManager();
        foreach ($uitslagregels as $uitslagregel) {
            if (strcmp(strtolower($uitslagregel['pos']), 'leader') === 0) {
                continue;
            }
            $uitslag = new Uitslag();
            $uitslag->setPloegPunten(0);
            $uitslag->setPositie($uitslagregel['pos']);
            $uitslag->setRennerPunten($uitslagregel['points']);
            $renner = $rennerRepo->findOneByCQId($uitslagregel['cqranking_id']);
            if ($renner !== null) {
                $uitslag->setRenner($renner);
                $transfer = $transferRepo->findLastTransferForDate($renner, $wedstrijd->getDatum());
                if ($transfer === null) {
                    $uitslag->setPloeg(null);
                } else {
                    $uitslag->setPloeg($transfer->getPloegNaar());
                }
                $canGetPoints = $this->puntenCalculator->canGetPoints($renner, $wedstrijd->getDatum(), $puntenReferentieDatum);
                if (null !== $uitslag->getPloeg() && $canGetPoints) {
                    $uitslag->setPloegPunten($uitslagregel['points']);
                }
            } else {
                $uitslag->setPloeg(null);
                $renner = $rennerManager->createRennerFromRennerSelectorTypeString(
                    $rennerManager->getRennerSelectorTypeString($uitslagregel['cqranking_id'], $uitslagregel['name']));
                $uitslag->setRenner($renner);
            }


            $uitslagen[] = $uitslag;
            $rows++;
            if ($rows == $maxResults) {
                break;
            }
        }
        return $uitslagen;
    }

    /**
     *
     * Enter description here ...
     * @param Form $form
     * @return array Uitslag
     */
    public function prepareUitslagen(Form $form, $crawler, $wedstrijd, $puntenReferentieDatum = null)
    {
        $url = $form->get('url')->getData();
        if (!$url) {
            $wedstrijdId = $form->get('cq_wedstrijdid')->getData();
            $url = $this->cqRankingWedstrijdUrl.$wedstrijdId;
        }
        $uitslagType = $form->get('uitslagtype')->getData();
        $parseStrategy = $uitslagType->getCqParsingStrategy();
        $uitslagregels = $this->cqParser->getResultRows($crawler, $parseStrategy);
        $rows = 0;
        $maxResults = $uitslagType->getMaxResults();
        $uitslagen = array();
        $rennerRepo = $this->entityManager->getRepository('CyclearGameBundle:Renner');
        $transferRepo = $this->entityManager->getRepository('CyclearGameBundle:Transfer');
        $rennerManager = new RennerManager();
        foreach ($uitslagregels as $uitslagregel) {
            if (strcmp(strtolower($uitslagregel['pos']), 'leader') === 0) {
                continue;
            }
            $uitslag = new Uitslag();
            $uitslag->setPloegPunten(0);
            $uitslag->setPositie($uitslagregel['pos']);
            $uitslag->setRennerPunten($uitslagregel['points']);
            $renner = $rennerRepo->findOneByCQId($uitslagregel['cqranking_id']);
            if ($renner !== null) {
                $uitslag->setRenner($renner);
                $transfer = $transferRepo->findLastTransferForDate($renner, $wedstrijd->getDatum());
                if ($transfer === null) {
                    $uitslag->setPloeg(null);
                } else {
                    $uitslag->setPloeg($transfer->getPloegNaar());
                }
                $canGetPoints = $this->puntenCalculator->canGetPoints($renner, $wedstrijd->getDatum(), $puntenReferentieDatum);
                if (null !== $uitslag->getPloeg() && $canGetPoints) {
                    $uitslag->setPloegPunten($uitslagregel['points']);
                }
            } else {
                $uitslag->setPloeg(null);
                $renner = $rennerManager->createRennerFromRennerSelectorTypeString(
                    $rennerManager->getRennerSelectorTypeString($uitslagregel['cqranking_id'], $uitslagregel['name']));
                $uitslag->setRenner($renner);
            }


            $uitslagen[] = $uitslag;
            $rows++;
            if ($rows == $maxResults) {
                break;
            }
        }
        return $uitslagen;
    }
}