<?php

namespace Cyclear\GameBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @Route("/game/{seizoen}/uitslag")
 */
class UitslagController extends Controller
{

    /**
     * @Route("/", name="uitslag_latest")
     * @Template()
     */
    public function latestAction(\Symfony\Component\HttpFoundation\Request $request)
    {
        $em = $this->getDoctrine()->getEntityManager();
        
        $uitslagenQb = $em->getRepository("CyclearGameBundle:Wedstrijd")->createQueryBuilder('u')->orderBy('u.datum','DESC');
        $paginator = $this->get('knp_paginator');
        $uitslagen = $paginator->paginate(
            $uitslagenQb, $this->get('request')->query->get('page', 1)/* page number */, 20/* limit per page */
        );
        return array('uitslagen' => $uitslagen, 'seizoen' => $request->get('seizoen'));
    }
    
    
    

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
            return new \Symfony\Component\HttpFoundation\RedirectResponse($this->generateUrl("uitslag_periode", array("seizoen" => $seizoen, "periode_id" => $periode->getId())));
        }
        $seizoen = $this->getDoctrine()->getRepository("CyclearGameBundle:Seizoen")->findBySlug($seizoen);

        $em = $this->getDoctrine()->getEntityManager();
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

        $em = $this->getDoctrine()->getEntityManager();
        $periode = $em->find("CyclearGameBundle:Periode", $periode_id);
        $list = $this->getDoctrine()->getRepository("CyclearGameBundle:Uitslag")->getPuntenByPloegForPeriode($periode, $seizoen[0]);
        $periodes = $this->getDoctrine()->getRepository("CyclearGameBundle:Periode")->findBy(array("seizoen" => $seizoen[0]));
        return array('list' => $list, 'seizoen' => $seizoen[0], 'periodes' => $periodes, 'current_periode' => $periode);
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
}
