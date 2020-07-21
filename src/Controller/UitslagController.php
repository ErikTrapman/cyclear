<?php

/*
 * This file is part of the Cyclear-game package.
 *
 * (c) Erik Trapman <veggatron@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller;

use App\Entity\Periode;
use App\Entity\Ploeg;
use App\Entity\Seizoen;
use App\Entity\Transfer;
use App\Entity\Uitslag;
use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/{seizoen}/uitslag")
 */
class UitslagController extends AbstractController
{

    /**
     * @Route("/periodes/{periode}", name="uitslag_periodes")
     * @ParamConverter("seizoen", options={"mapping": {"seizoen": "slug"}})
     * @Template()
     */
    public function periodesAction(Seizoen $seizoen, Periode $periode)
    {
        $em = $this->getDoctrine()->getManager();
        $list = $this->getDoctrine()->getRepository(Uitslag::class)->getPuntenByPloegForPeriode($periode, $seizoen);
        $periodes = $this->getDoctrine()->getRepository(Periode::class)->findBy(array("seizoen" => $seizoen));


        $gainedTransferpoints = [];
        foreach ($em->getRepository(Uitslag::class)
                     ->getPuntenByPloegForUserTransfersWithoutLoss($seizoen, $periode->getStart(), $periode->getEind()) as $teamResult) {
            $gainedTransferpoints[$teamResult['id']] = $teamResult['punten'];
        }
        $lostDraftPoints = [];
        foreach ($em->getRepository(Uitslag::class)->getLostDraftPuntenByPloeg($seizoen, $periode->getStart(), $periode->getEind()) as $teamResult) {
            if ($teamResult instanceof Ploeg) {
                $lostDraftPoints[$teamResult->getId()] = $teamResult->getPunten();
            } else {
                $lostDraftPoints[$teamResult['id']] = $teamResult['punten'];
            }
        }
        $transferSaldo = [];
        foreach ($gainedTransferpoints as $teamId => $gainedPoints) {
            $transferSaldo[$teamId] = $gainedPoints - $lostDraftPoints[$teamId];
        }
        $zegesInPeriode = [];
        foreach ($em->getRepository(Uitslag::class)->getCountForPosition($seizoen, 1, $periode->getStart(), $periode->getEind()) as $teamResult) {
            $zegesInPeriode[$teamResult[0]->getId()] = $teamResult['freqByPos'];
        }

        return array(
            'list' => $list,
            'seizoen' => $seizoen,
            'periodes' => $periodes,
            'periode' => $periode,
            'transferpoints' => $transferSaldo,
            'positionCount' => $zegesInPeriode,
            'transferRepo' => $em->getRepository(Transfer::class));
    }

    /**
     * @Route("/posities/{positie}", name="uitslag_posities")
     * @ParamConverter("seizoen", options={"mapping": {"seizoen": "slug"}})
     * @Template()
     */
    public function positiesAction(Request $request, Seizoen $seizoen, $positie = 1)
    {
        $list = $this->getDoctrine()->getRepository(Uitslag::class)->getCountForPosition($seizoen, $positie);
        return array('list' => $list, 'seizoen' => $seizoen, 'positie' => $positie);
    }

    /**
     * @Route("/draft-klassement", name="uitslag_draft")
     * @ParamConverter("seizoen", options={"mapping": {"seizoen": "slug"}})
     * @Template()
     */
    public function viewByDraftTransferAction(Seizoen $seizoen)
    {
        $list = $this->getDoctrine()->getRepository(Uitslag::class)->getPuntenByPloegForDraftTransfers($seizoen);
        return array('list' => $list, 'seizoen' => $seizoen);
    }

    /**
     * @Route("/transfer-klassement", name="uitslag_transfers")
     * @ParamConverter("seizoen", options={"mapping": {"seizoen": "slug"}})
     * @Template()
     */
    public function viewByUserTransferAction(Seizoen $seizoen)
    {
        $seizoen = $this->getDoctrine()->getRepository(Seizoen::class)->findBySlug($seizoen);
        $list = $this->getDoctrine()->getRepository(Uitslag::class)->getPuntenByPloegForUserTransfers($seizoen);
        return array('list' => $list, 'seizoen' => $seizoen);
    }

    /**
     * @Route("/overzicht", name="uitslag_overview")
     * @ParamConverter("seizoen", options={"mapping": {"seizoen": "slug"}})
     * @Template()
     */
    public function overviewAction(Request $request, Seizoen $seizoen)
    {
        /** @var EntityManager $em */
        $em = $this->get('doctrine.orm.default_entity_manager');
        $uitslagRepo = $em->getRepository(Uitslag::class);
        $transfer = $uitslagRepo->getPuntenByPloegForUserTransfers($seizoen);

        $gained = array();
        foreach ($uitslagRepo->getPuntenByPloegForUserTransfersWithoutLoss($seizoen) as $teamResult) {
            $gained[$teamResult['id']] = $teamResult['punten'];
        }
        $lost = array();
        foreach ($uitslagRepo->getLostDraftPuntenByPloeg($seizoen) as $teamResult) {
            if ($teamResult instanceof Ploeg) {
                $lost[$teamResult->getId()] = $teamResult->getPunten();
            } else {
                $lost[$teamResult['id']] = $teamResult['punten'];
            }
        }
        $stand = $uitslagRepo->getPuntenByPloeg($seizoen);
        $draft = $uitslagRepo->getPuntenByPloegForDraftTransfers($seizoen);
        $transferRepo = $em->getRepository(Transfer::class);

        $bestTransfers = array_slice($uitslagRepo->getBestTransfers($seizoen), 0, 50);

        return array(
            'seizoen' => $seizoen,
            'transfer' => $transfer,
            'shadowgained' => $gained,
            'shadowlost' => $lost,
            'stand' => $stand,
            'draft' => $draft,
            'transferRepo' => $transferRepo,
            'bestTransfers' => $bestTransfers);
    }

}
