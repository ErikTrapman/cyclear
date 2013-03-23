<?php

namespace Cyclear\GameBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/{seizoen}/uitslag")
 */
class UitslagController extends Controller
{

    /**
     * @Route("/zeges", name="uitslag_zeges")
     * @Template()
     */
    public function viewByPositionAction($seizoen, $pos = 1)
    {
        $seizoen = $this->getDoctrine()->getRepository("CyclearGameBundle:Seizoen")->findBySlug($seizoen);
        $list = $this->getDoctrine()->getRepository("CyclearGameBundle:Uitslag")->getCountForPosition($seizoen[0], $pos);
        return array('list' => $list, 'seizoen' => $seizoen[0]);
    }

    /**
     * @Route("/stand", name="uitslag_stand")
     * @Template()
     */
    public function viewByPloegenAction($seizoen)
    {
        $seizoen = $this->getDoctrine()->getRepository("CyclearGameBundle:Seizoen")->findBySlug($seizoen);
        $list = $this->getDoctrine()->getRepository("CyclearGameBundle:Uitslag")->getPuntenByPloeg($seizoen[0]);
        return array('list' => $list, 'seizoen' => $seizoen[0]);
    }

    /**
     * @Route("/periode/{periode_id}", name="uitslag_periode")
     * @Template()
     */
    public function viewByPeriodeAction($seizoen = null, $periode_id = null)
    {
        if ($periode_id === null) {
            $periode = $this->getDoctrine()->getRepository("CyclearGameBundle:Periode")->getCurrentPeriode();
            return new RedirectResponse($this->generateUrl("uitslag_periode", array("seizoen" => $seizoen, "periode_id" => $periode->getId())));
        }
        $seizoen = $this->getDoctrine()->getRepository("CyclearGameBundle:Seizoen")->findBySlug($seizoen);

        $em = $this->getDoctrine()->getManager();
        $periode = $em->find("CyclearGameBundle:Periode", $periode_id);
        $list = $this->getDoctrine()->getRepository("CyclearGameBundle:Uitslag")->getPuntenByPloegForPeriode($periode, $seizoen[0]);
        return array('list' => $list, 'seizoen' => $seizoen[0], 'periode' => $periode);
    }

    /**
     * @Route("/periodes/{periode_id}", name="uitslag_periodes")
     * @Template()
     */
    public function periodesAction($seizoen, $periode_id)
    {
        $seizoen = $this->getDoctrine()->getRepository("CyclearGameBundle:Seizoen")->findBySlug($seizoen);

        $em = $this->getDoctrine()->getManager();
        $periode = $em->find("CyclearGameBundle:Periode", $periode_id);
        $list = $this->getDoctrine()->getRepository("CyclearGameBundle:Uitslag")->getPuntenByPloegForPeriode($periode, $seizoen[0]);
        $periodes = $this->getDoctrine()->getRepository("CyclearGameBundle:Periode")->findBy(array("seizoen" => $seizoen[0]));
        return array('list' => $list, 'seizoen' => $seizoen[0], 'periodes' => $periodes, 'periode' => $periode, 'transferRepo' => $em->getRepository("CyclearGameBundle:Transfer"));
    }

    /**
     * @Route("/posities/{positie}", name="uitslag_posities")
     * @Template()
     */
    public function positiesAction(Request $request, $positie = 1)
    {
        $seizoen = $request->attributes->get('seizoen-object');
        $list = $this->getDoctrine()->getRepository("CyclearGameBundle:Uitslag")->getCountForPosition($seizoen, $positie);
        return array('list' => $list, 'seizoen' => $seizoen, 'positie' => $positie);
    }

    /**
     * @Route("/draft-klassement", name="uitslag_draft")
     * @Template()
     */
    public function viewByDraftTransferAction($seizoen)
    {
        $seizoen = $this->getDoctrine()->getRepository("CyclearGameBundle:Seizoen")->findBySlug($seizoen);
        $list = $this->getDoctrine()->getRepository("CyclearGameBundle:Uitslag")->getPuntenByPloegForDraftTransfers($seizoen[0]);
        return array('list' => $list, 'seizoen' => $seizoen[0]);
    }

    /**
     * @Route("/transfer-klassement", name="uitslag_transfers")
     * @Template()
     */
    public function viewByUserTransferAction($seizoen)
    {
        $seizoen = $this->getDoctrine()->getRepository("CyclearGameBundle:Seizoen")->findBySlug($seizoen);
        $list = $this->getDoctrine()->getRepository("CyclearGameBundle:Uitslag")->getPuntenByPloegForUserTransfers($seizoen[0]);
        return array('list' => $list, 'seizoen' => $seizoen[0]);
    }

    /**
     * @Route("/overzicht", name="uitslag_overview")
     * @Template()
     */
    public function overviewAction(Request $request)
    {
        $seizoen = $request->attributes->get('seizoen-object');
        $transfer = $this->getDoctrine()->getRepository("CyclearGameBundle:Uitslag")->getPuntenByPloegForUserTransfers($seizoen);
        $stand = $this->getDoctrine()->getRepository("CyclearGameBundle:Uitslag")->getPuntenByPloeg($seizoen);
        $draft = $this->getDoctrine()->getRepository("CyclearGameBundle:Uitslag")->getPuntenByPloegForDraftTransfers($seizoen);
        return array('seizoen' => $seizoen, 'transfer' => $transfer, 'stand' => $stand, 'draft' => $draft);
    }
}
