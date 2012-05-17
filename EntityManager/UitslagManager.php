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

    public function __construct(EntityManager $em, $cqParser, \Cyclear\GameBundle\Calculator\PuntenCalculator $puntenCalculator) {
        $this->entityManager = $em;
        $this->cqParser = $cqParser;
        $this->puntenCalculator = $puntenCalculator;
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

        $parser = $this->cqParser; // new CQParser(new Crawler); // TODO make service
        $uitslagregels = $parser->getResultRows($url, $form->get('uitslagtype')->getData()->getCqParsingStrategy());
        $rows = 0;
        $maxResults = $form->get('uitslagtype')->getData()->getMaxResults();

        $puntenReferentieDatum = $form->get('datum')->getData();
        // TODO if referentiewedstrijd: get datum

        $uitslagen = array();
        $rennerRepo = $this->entityManager->getRepository('CyclearGameBundle:Renner');
        foreach ($uitslagregels as $uitslagregel) {

            if (strcmp(strtolower($uitslagregel['positie']), 'leader') === 0) {
                continue;
            }

            $uitslag = new Uitslag();

            $renner = $rennerRepo->findOneByCQId($uitslagregel['cqranking_id']);
            if ($renner !== null) {
                $uitslag->setRenner($renner);
                $rennerLookup = $rennerRepo->findOneJoinedByPloegOnDate($renner, $puntenReferentieDatum);
                if ($rennerLookup !== null && count($rennerLookup) == 1 ) {
                    
                    echo $rennerLookup[0]->getPloeg()->getId();die(__METHOD__);
                    
                    $uitslag->setPloeg($rennerLookup[0]->getPloeg());
                } else {
                    $uitslag->setPloeg(null);
                }
            } else {
                $uitslag->setPloeg(null);
                $renner = new \Cyclear\GameBundle\Entity\Renner();
                $renner->setNaam($uitslagregel['naam']);
                $renner->setCqRanking_id($uitslagregel['cqranking_id']);
                $uitslag->setRenner($renner);
            }
            $uitslag->setPositie($uitslagregel['positie']);

            if ($this->puntenCalculator->canGetPoints($renner, $puntenReferentieDatum)) {
                $uitslag->setPunten($uitslagregel['punten']);
            } else {
                $uitslag->setPunten(0);
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