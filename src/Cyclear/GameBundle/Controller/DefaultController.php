<?php
/*
 * This file is part of the Cyclear-game package.
 *
 * (c) Erik Trapman <veggatron@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cyclear\GameBundle\Controller;

use Cyclear\GameBundle\Entity\Ploeg;
use Cyclear\GameBundle\Entity\Seizoen;
use Cyclear\GameBundle\Entity\Transfer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;

/**
 *
 * @Route("/{seizoen}")
 */
class DefaultController extends Controller
{

    /**
     * @Route("/", name="game")
     * @ParamConverter("seizoen", options={"mapping": {"seizoen": "slug"}})
     * @Template()
     */
    public function indexAction(Request $request, Seizoen $seizoen)
    {
        $doctrine = $this->getDoctrine();
        $periode = $this->getDoctrine()->getRepository("CyclearGameBundle:Periode")->getCurrentPeriode($seizoen);
        $nieuws = $this->getDoctrine()->getRepository("CyclearGameBundle:Nieuws")->findBy(array('seizoen' => $seizoen), array('id' => 'DESC'), 1);
        $stand = $doctrine->getRepository("CyclearGameBundle:Uitslag")->getPuntenByPloeg($seizoen);
        if (!array_key_exists(0, $nieuws)) {
            $nieuws = null;
        } else {
            $nieuws = $nieuws[0];
        }
        $shadowStandingsById = array();
        if (null !== $periode) {
            $refdate = $periode->getStart();
        } else {
            $refdate = new \DateTime;
        }
        foreach ($doctrine->getRepository("CyclearGameBundle:Uitslag")->getPuntenByPloeg($seizoen, null, $refdate) as $key => $value) {
            $value['position'] = $key + 1;
            $shadowStandingsById[$value[0]->getId()] = $value;
        }
        $wedstrijden = $doctrine->getRepository("CyclearGameBundle:Wedstrijd")->createQueryBuilder('w')
            ->where('w.seizoen = :seizoen')->setParameter('seizoen', $seizoen)
            ->orderBy('w.datum DESC, w.id', 'DESC')
            ->setMaxResults(20)->getQuery()->getResult();
        $periodestand = $periode ? $doctrine->getRepository("CyclearGameBundle:Uitslag")->getPuntenByPloegForPeriode($periode, $seizoen) : [];
        $gainedTransferpoints = [];
        if ($periode) {
            foreach ($doctrine->getRepository('CyclearGameBundle:Uitslag')
                         ->getPuntenByPloegForUserTransfersWithoutLoss($seizoen, $periode->getStart(), $periode->getEind()) as $teamResult) {
                $gainedTransferpoints[$teamResult['id']] = $teamResult['punten'];
            }
        }
        $lostDraftPoints = [];
        if ($periode) {
            foreach ($doctrine->getRepository('CyclearGameBundle:Uitslag')->getLostDraftPuntenByPloeg($seizoen, $periode->getStart(), $periode->getEind()) as $teamResult) {
                if ($teamResult instanceof Ploeg) {
                    $lostDraftPoints[$teamResult->getId()] = $teamResult->getPunten();
                } else {
                    $lostDraftPoints[$teamResult['id']] = $teamResult['punten'];
                }
            }
        }
        $transferSaldo = [];
        foreach ($gainedTransferpoints as $teamId => $gainedPoints) {
            $transferSaldo[$teamId] = $gainedPoints - $lostDraftPoints[$teamId];
        }
        $zeges = $doctrine->getRepository("CyclearGameBundle:Uitslag")->getCountForPosition($seizoen, 1);
        $zegesInPeriode = [];
        if ($periode) {
            foreach ($doctrine->getRepository('CyclearGameBundle:Uitslag')->getCountForPosition($seizoen, 1, $periode->getStart(), $periode->getEind()) as $teamResult) {
                $zegesInPeriode[$teamResult[0]->getId()] = $teamResult['freqByPos'];
            }
        }
        $draft = $doctrine->getRepository("CyclearGameBundle:Uitslag")->getPuntenByPloegForDraftTransfers($seizoen);
        $transfers = $this->getDoctrine()->getRepository("CyclearGameBundle:Transfer")
            ->getLatest($seizoen, array(Transfer::ADMINTRANSFER, Transfer::USERTRANSFER), 20);
        $transferRepo = $this->getDoctrine()->getRepository("CyclearGameBundle:Transfer");
        return array(
            'periode' => $periode,
            'seizoen' => $seizoen,
            'nieuws' => $nieuws,
            'stand' => $stand,
            'shadowstandingsById' => $shadowStandingsById,
            'wedstrijden' => $wedstrijden,
            'periodestand' => $periodestand,
            'transferpuntenPeriode' => $transferSaldo,
            'zegestand' => $zeges,
            'zegesInPeriode' => $zegesInPeriode,
            'drafts' => $draft,
            'transfers' => $transfers,
            'transferRepo' => $transferRepo
        );
    }

    /**
     * @param Request $request
     *
     * @Route("/startlists")
     * @Template
     */
    public function startlistsAction(Request $request)
    {
        $riders = [
            139446,
            139652,
            134807,
            139811,
            168776,
            138849,
            135000,
            140704,
            140149,
            137768,
            140466,
            140245,
            140332
        ];
        $baseUrl = 'http://www.procyclingstats.com/rider.php?id=%d';
        $ret = [];
        foreach ($riders as $rider) {

            $crawler = new Crawler();
            $crawler->addContent(file_get_contents(sprintf($baseUrl, $rider)), 'text/html');

            $riderValues = [];

            $name = $crawler->filter('.entryHeader')->filter('h1')->getNode(0)->nodeValue;
            $riderValues['name'] = $name;
            $riderValues['courses'] = [];

            try {
                $interestingNodes = $crawler->filter('.section')->first()->siblings();
                $interestingNodes->last()->children()->filter('a')->each(
                    function (Crawler $node, $i) use (&$riderValues) {
                        foreach ($node->getIterator() as $item) {
                            if ('more' !== $item->nodeValue) {
                                $riderValues['courses'][] = $item->nodeValue;
                            }
                        }
                    });

            } catch (\InvalidArgumentException $e) {

            }
            $ret[$rider] = $riderValues;
        }

        uasort($ret, function ($a, $b) {
            return $a['name'] > $b['name'] ? 1 : -1;
        });

        return [
            'riders' => $ret
        ];
    }
}