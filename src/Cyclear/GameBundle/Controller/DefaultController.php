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

use Cyclear\GameBundle\Entity\Seizoen;
use Cyclear\GameBundle\Entity\Transfer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
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
        $periode = $this->getDoctrine()->getRepository("CyclearGameBundle:Periode")->getCurrentPeriode($seizoen);

        $nieuws = $this->getDoctrine()->getRepository("CyclearGameBundle:Nieuws")->findBy(array('seizoen' => $seizoen), array('id' => 'DESC'), 1);
        if (!array_key_exists(0, $nieuws)) {
            $nieuws = null;
        } else {
            $nieuws = $nieuws[0];
        }
        $doctrine = $this->getDoctrine();
        $stand = $doctrine->getRepository("CyclearGameBundle:Uitslag")->getPuntenByPloeg($seizoen);
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
        // TODO: dit naar repository-class
        $wedstrijden = $doctrine->getRepository("CyclearGameBundle:Wedstrijd")->createQueryBuilder('w')
            ->where('w.seizoen = :seizoen')->setParameter('seizoen', $seizoen)
            ->orderBy('w.datum DESC, w.id', 'DESC')
            ->setMaxResults(20)->getQuery()->getResult();
        $periodestand = $doctrine->getRepository("CyclearGameBundle:Uitslag")->getPuntenByPloegForPeriode($periode, $seizoen);
        $gainedTransferpoints = [];
        foreach ($doctrine->getRepository('CyclearGameBundle:Uitslag')
                     ->getPuntenByPloegForUserTransfersWithoutLoss($seizoen, $periode->getStart(), $periode->getEind()) as $teamResult) {
            $gainedTransferpoints[$teamResult['id']] = $teamResult['punten'];
        }
        $lostDraftPoints = [];
        foreach ($doctrine->getRepository('CyclearGameBundle:Uitslag')->getLostDraftPuntenByPloeg($seizoen, $periode->getStart(), $periode->getEind()) as $teamResult) {
            $lostDraftPoints[$teamResult['id']] = $teamResult['punten'];
        }
        $transferSaldo = [];
        foreach ($gainedTransferpoints as $teamId => $gainedPoints) {
            $transferSaldo[$teamId] = $gainedPoints - $lostDraftPoints[$teamId];
        }
        $zeges = $doctrine->getRepository("CyclearGameBundle:Uitslag")->getCountForPosition($seizoen, 1);
        $zegesInPeriode = [];
        foreach ($doctrine->getRepository('CyclearGameBundle:Uitslag')->getCountForPosition($seizoen, 1, $periode->getStart(), $periode->getEind()) as $teamResult) {
            $zegesInPeriode[$teamResult[0]->getId()] = $teamResult['freqByPos'];
        }
        $draft = $doctrine->getRepository("CyclearGameBundle:Uitslag")->getPuntenByPloegForDraftTransfers($seizoen);
        //$transferstand = $doctrine->getRepository("CyclearGameBundle:Uitslag")->getPuntenByPloegForUserTransfers($seizoen);
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
            //'transferstand' => $transferstand,
            'transfers' => $transfers,
            'transferRepo' => $transferRepo
        );
    }
}