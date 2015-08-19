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

use Cyclear\GameBundle\Entity\Periode;
use Cyclear\GameBundle\Entity\Seizoen;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/{seizoen}/uitslag")
 */
class UitslagController extends Controller
{

    /**
     * @Route("/periodes/{periode}", name="uitslag_periodes")
     * @ParamConverter("seizoen", options={"mapping": {"seizoen": "slug"}})
     * @Template()
     */
    public function periodesAction(Seizoen $seizoen, Periode $periode)
    {
        $em = $this->getDoctrine()->getManager();
        $list = $this->getDoctrine()->getRepository("CyclearGameBundle:Uitslag")->getPuntenByPloegForPeriode($periode, $seizoen);
        $periodes = $this->getDoctrine()->getRepository("CyclearGameBundle:Periode")->findBy(array("seizoen" => $seizoen));
        return array('list' => $list, 'seizoen' => $seizoen, 'periodes' => $periodes, 'periode' => $periode, 'transferRepo' => $em->getRepository("CyclearGameBundle:Transfer"));
    }

    /**
     * @Route("/posities/{positie}", name="uitslag_posities")
     * @ParamConverter("seizoen", options={"mapping": {"seizoen": "slug"}})
     * @Template()
     */
    public function positiesAction(Request $request, Seizoen $seizoen, $positie = 1)
    {
        $list = $this->getDoctrine()->getRepository("CyclearGameBundle:Uitslag")->getCountForPosition($seizoen, $positie);
        return array('list' => $list, 'seizoen' => $seizoen, 'positie' => $positie);
    }

    /**
     * @Route("/draft-klassement", name="uitslag_draft")
     * @ParamConverter("seizoen", options={"mapping": {"seizoen": "slug"}})
     * @Template()
     */
    public function viewByDraftTransferAction(Seizoen $seizoen)
    {
        $list = $this->getDoctrine()->getRepository("CyclearGameBundle:Uitslag")->getPuntenByPloegForDraftTransfers($seizoen);
        return array('list' => $list, 'seizoen' => $seizoen);
    }

    /**
     * @Route("/transfer-klassement", name="uitslag_transfers")
     * @ParamConverter("seizoen", options={"mapping": {"seizoen": "slug"}})
     * @Template()
     */
    public function viewByUserTransferAction(Seizoen $seizoen)
    {
        $seizoen = $this->getDoctrine()->getRepository("CyclearGameBundle:Seizoen")->findBySlug($seizoen);
        $list = $this->getDoctrine()->getRepository("CyclearGameBundle:Uitslag")->getPuntenByPloegForUserTransfers($seizoen);
        return array('list' => $list, 'seizoen' => $seizoen);
    }

    /**
     * @Route("/overzicht", name="uitslag_overview")
     * @ParamConverter("seizoen", options={"mapping": {"seizoen": "slug"}})
     * @Template()
     */
    public function overviewAction(Request $request, Seizoen $seizoen)
    {
        $transfer = $this->getDoctrine()->getRepository("CyclearGameBundle:Uitslag")->getPuntenByPloegForUserTransfers($seizoen);

        $gained = array();
        foreach ($this->getDoctrine()->getRepository("CyclearGameBundle:Uitslag")->getPuntenByPloegForUserTransfersWithoutLoss($seizoen) as $teamResult) {
            $gained[$teamResult['id']] = $teamResult['punten'];
        }
        $lost = array();
        foreach ($this->getDoctrine()->getRepository('CyclearGameBundle:Uitslag')->getLostDraftPuntenByPloeg($seizoen) as $teamResult) {
            $lost[$teamResult['id']] = $teamResult['punten'];
        }
        $stand = $this->getDoctrine()->getRepository("CyclearGameBundle:Uitslag")->getPuntenByPloeg($seizoen);
        $draft = $this->getDoctrine()->getRepository("CyclearGameBundle:Uitslag")->getPuntenByPloegForDraftTransfers($seizoen);
        return array(
            'seizoen' => $seizoen,
            'transfer' => $transfer,
            'shadowgained' => $gained,
            'shadowlost' => $lost,
            'stand' => $stand,
            'draft' => $draft);
    }
}
