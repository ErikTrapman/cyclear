<?php

namespace Cyclear\GameBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @Route("/uitslag")
 */
class UitslagController extends Controller {

    /**
     * @Route("/zeges", name="uitslag_zeges")
     * @Template()
     */
    public function viewByPositionAction($pos = 1) {
        $list = $this->getDoctrine()->getRepository("CyclearGameBundle:Uitslag")->getCountForPosition($pos);
        return array('list' => $list);
    }
    
    /**
     * @Route("/stand", name="uitslag_stand")
     * @Template()
     */
    public function viewByPloegenAction() {
        $list = $this->getDoctrine()->getRepository("CyclearGameBundle:Uitslag")->getPuntenByPloeg();
        return array('list' => $list);
    }
    
    /**
     * @Route("/periode/{periode_id}", name="uitslag_periode")
     * @Template()
     */
    public function viewByPeriodeAction($periode_id = null){
        
        if($periode_id === null){
            $periode = $this->getDoctrine()->getRepository("CyclearGameBundle:Periode")->getCurrentPeriode();
            return new \Symfony\Component\HttpFoundation\RedirectResponse($this->generateUrl("uitslag_periode", array("periode_id" => $periode->getId())));
        }
        $em = $this->getDoctrine()->getEntityManager();
        $periode = $em->find("CyclearGameBundle:Periode", $periode_id);
        $list = $this->getDoctrine()->getRepository("CyclearGameBundle:Uitslag")->getPuntenByPloegForPeriode($periode);
        return array('list' => $list);
    }

}
