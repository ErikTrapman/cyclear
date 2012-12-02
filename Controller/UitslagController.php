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
     * @Route("/zeges", name="uitslag_zeges")
     * @Template()
     */
    public function viewByPositionAction($seizoen, $pos = 1)
    {
        $seizoen = $this->getDoctrine()->getRepository("CyclearGameBundle:Seizoen")->findBySlug($seizoen);
        $list = $this->getDoctrine()->getRepository("CyclearGameBundle:Uitslag")->getCountForPosition( $seizoen[0], $pos);
        return array('list' => $list);
    }

    /**
     * @Route("/stand", name="uitslag_stand")
     * @Template()
     */
    public function viewByPloegenAction($seizoen)
    {
        $seizoen = $this->getDoctrine()->getRepository("CyclearGameBundle:Seizoen")->findBySlug($seizoen);
        $list = $this->getDoctrine()->getRepository("CyclearGameBundle:Uitslag")->getPuntenByPloeg($seizoen[0]);
        return array('list' => $list);
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
        return array('list' => $list);
    }
}
