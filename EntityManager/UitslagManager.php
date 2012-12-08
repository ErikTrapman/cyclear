<?php

namespace Cyclear\GameBundle\EntityManager;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\DoctrineBundle\Registry;
use Symfony\Component\CssSelector\CssSelector;
use Symfony\Component\Form\Form;
use Symfony\Bundle\SecurityBundle\Tests\Functional\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Cyclear\GameBundle\Form\UitslagNewType,
    Cyclear\GameBundle\Parser\CQParser,
    Cyclear\GameBundle\Form\UitslagType,
    Cyclear\GameBundle\Form\UitslagConfirmType,
    Cyclear\GameBundle\Entity\Uitslag,
    Cyclear\GameBundle\Calculator\PuntenCalculator;

class UitslagManager {

    /**
     * 
     * @var EntityManager
     */
    private $entityManager;
    private $puntenCalculator;
    private $cqParser;

    public function __construct(EntityManager $em, $parser, \Cyclear\GameBundle\Calculator\PuntenCalculator $puntenCalculator) {
        $this->entityManager = $em;
        $this->puntenCalculator = $puntenCalculator;
        $this->cqParser = $parser;
    }

    /**
     *
     * Enter description here ...
     * @param Form $form
     * @return array Uitslag
     */
    public function prepareUitslagen(Form $form) {

        $url = $form->get('url')->getData();
        $dateTime = $form->get('datum')->getData();

        //$parseStrategyClassname = $form->get('uitslagtype')->getData()->getCqParsingStrategy();
        $parseStrategy = $form->get('uitslagtype')->getData();
        $uitslagregels = $this->cqParser->getResultRows($url, $parseStrategy);
        $rows = 0;
        $maxResults = $form->get('uitslagtype')->getData()->getMaxResults();

        $puntenReferentieDatum = new \DateTime('2011-10-15'); // $form->get('datum')->getData();
        // TODO if referentiewedstrijd: get datum

        $uitslagen = array();
        $rennerRepo = $this->entityManager->getRepository('CyclearGameBundle:Renner');
        $transferRepo = $this->entityManager->getRepository('CyclearGameBundle:Transfer');
        foreach ($uitslagregels as $uitslagregel) {

            if (strcmp(strtolower($uitslagregel['pos']), 'leader') === 0) {
                continue;
            }

            $uitslag = new Uitslag();

            $renner = $rennerRepo->findOneByCQId($uitslagregel['cqranking_id']);
            if ($renner !== null) {
                $uitslag->setRenner($renner);

                $transfer = $transferRepo->findLastTransferForDate($renner, $puntenReferentieDatum);
                if ($transfer === null) {
                    $uitslag->setPloeg(null);
                } else {
                    $uitslag->setPloeg($transfer->getPloegNaar());
                }
//                $rennerLookup = $rennerRepo->findOneJoinedByPloegOnDate($renner, $puntenReferentieDatum);
//                if ($rennerLookup !== null && count($rennerLookup) == 1 ) {
//                    
//                    echo $rennerLookup[0]->getPloeg()->getId();die(__METHOD__);
//                    
//                    $uitslag->setPloeg($rennerLookup[0]->getPloeg());
//                } else {
//                    $uitslag->setPloeg(null);
//                }
            } else {
                $uitslag->setPloeg(null);
                $renner = new \Cyclear\GameBundle\Entity\Renner();
                $renner->setNaam($uitslagregel['name']);
                $renner->setCqRanking_id($uitslagregel['cqranking_id']);
                $uitslag->setRenner($renner);
            }
            $uitslag->setPositie($uitslagregel['pos']);
            $uitslag->setRennerPunten($uitslagregel['points']);
            if ($this->puntenCalculator->canGetPoints($renner, $puntenReferentieDatum)) {
                $uitslag->setPloegPunten($uitslagregel['points']);
            } else {
                $uitslag->setPloegPunten(0);
            }

            $uitslag->setDatum(new \DateTime());
            $uitslagen[] = $uitslag;
            $rows++;
            if ($rows == $maxResults) {
                break;
            }
        }
        return $uitslagen;
    }

}