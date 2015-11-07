<?php

/*
 * This file is part of the Cyclear-game package.
 *
 * (c) Erik Trapman <veggatron@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cyclear\GameBundle\CQ;

use Cyclear\GameBundle\CQ\Exception\CyclearGameBundleCQException;
use Cyclear\GameBundle\CQ\Exception\NotEnoughContentException;
use Cyclear\GameBundle\Entity\Seizoen;
use Cyclear\GameBundle\Entity\Uitslag;
use Cyclear\GameBundle\Entity\Wedstrijd;
use Cyclear\GameBundle\EntityManager\RennerManager;
use Cyclear\GameBundle\EntityManager\UitslagManager;
use Cyclear\GameBundle\Form\DataTransformer\RennerNameToRennerIdTransformer;
use Doctrine\ORM\EntityManager;
use ErikTrapman\Bundle\CQRankingParserBundle\Parser\Crawler\CrawlerManager;
use ErikTrapman\Bundle\CQRankingParserBundle\Parser\Exception\CQParserException;
use ErikTrapman\Bundle\CQRankingParserBundle\Parser\RecentRaces\RecentRacesParser;
use Symfony\Component\HttpKernel\Log\LoggerInterface;

class CQAutomaticResultsResolver
{

    /**
     * @var RecentRacesParser
     */
    private $parser;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var RaceCategoryMatcher
     */
    private $categoryMatcher;

    /**
     * @var UitslagManager
     */
    private $uitslagManager;

    /**
     * @var CrawlerManager
     */
    private $crawlerManager;

    /**
     * @var RennerManager
     */
    private $rennerManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(RecentRacesParser $parser,
                                EntityManager $em,
                                RaceCategoryMatcher $raceCategoryMatcher,
                                UitslagManager $uitslagManager,
                                CrawlerManager $crawlerManager,
                                LoggerInterface $logger)
    {
        $this->parser = $parser;
        $this->em = $em;
        $this->categoryMatcher = $raceCategoryMatcher;
        $this->uitslagManager = $uitslagManager;
        $this->crawlerManager = $crawlerManager;
        $this->rennerManager = new RennerManager();
        $this->logger = $logger;
    }

    /**
     * @param Seizoen $seizoen
     * @param \DateTime $upTo
     * @param int $max
     * @throws CyclearGameBundleCQException
     * @throws NotEnoughContentException
     *
     * Resolves results with RecentRacesParser.
     *
     * @return Wedstrijd[]
     */
    public function resolve(Seizoen $seizoen, \DateTime $upTo, $max = 1)
    {
        $races = $this->parser->getRecentRaces();
        $repo = $this->em->getRepository('CyclearGameBundle:Wedstrijd');
        $ploegRepo = $this->em->getRepository('CyclearGameBundle:Ploeg');
        $ret = [];
        foreach ($races as $i => $race) {

            // we simply use the url as external identifier
            $wedstrijd = $repo->findOneByExternalIdentifier($race->url);
            if ($wedstrijd) {
                continue;
            }
            if ($race->date > $upTo) {
                continue;
            }
            $wedstrijd = new Wedstrijd();
            $wedstrijd->setDatum(clone $race->date);
            $wedstrijd->setExternalIdentifier($race->url);
            $wedstrijd->setNaam($race->name);
            $wedstrijd->setSeizoen($seizoen);

            $type = $this->categoryMatcher->getUitslagTypeAccordingToCategory($race->category);
            if (null === $type) {
                $this->logger->notice('Unable to resolve uitslagtype from category `' . $race->category . '``');
                continue;
            }
            $wedstrijd->setGeneralClassification($type->isGeneralClassification());
            $wedstrijdRefDate = null;
            if ($this->categoryMatcher->needsRefStage($wedstrijd)) {
                $this->categoryMatcher->getRefStage($wedstrijd);
            }
            // TODO parametrize CQ-urls!
            $uitslagen = $this->uitslagManager->prepareUitslagen(
                $type,
                $this->crawlerManager->getCrawler('http://cqranking.com/men/asp/gen/' . $race->url),
                $wedstrijd,
                $seizoen);
            if (count($uitslagen) < $type->getMaxResults()) {
                $this->logger->notice($race->url . ' has not enough content yet');
                continue;
            }
            foreach ($uitslagen as $uitslagRow) {
                $uitslag = new Uitslag();
                $uitslag->setWedstrijd($wedstrijd);
                $uitslag->setPloeg($uitslagRow['ploeg'] ? $ploegRepo->find($uitslagRow['ploeg']) : null);
                $uitslag->setRennerPunten($uitslagRow['rennerPunten']);
                $uitslag->setPloegPunten($uitslagRow['ploegPunten']);
                $uitslag->setPositie($uitslagRow['positie']);
                $t = new RennerNameToRennerIdTransformer($this->em, $this->rennerManager);
                $uitslag->setRenner($t->reverseTransform($uitslagRow['renner']));
                $wedstrijd->addUitslag($uitslag);
            }
            $ret[] = $wedstrijd;
            if (count($ret) == $max) {
                break;
            }
        }
        return $ret;
    }

}