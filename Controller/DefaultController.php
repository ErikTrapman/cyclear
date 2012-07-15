<?php

namespace Cyclear\GameBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 *
 * @Route("/game")
 */
class DefaultController extends Controller {

    /**
     * @Route("/{seizoen}", name="game")
     * @Template()
     */
    public function indexAction($seizoen = null) {
        
        if (null === $seizoen) {
            $seizoen = $this->getDoctrine()->getRepository("CyclearGameBundle:Seizoen")->findOneBy(array("current" => 1));
            return new \Symfony\Component\HttpFoundation\RedirectResponse($this->generateUrl("game", array("seizoen" => $seizoen->getSlug())));
        }

        $seizoen = $this->getDoctrine()->getRepository("CyclearGameBundle:Seizoen")->findBySlug($seizoen);
        if(null === $seizoen){
            throw new \Doctrine\ORM\EntityNotFoundException("Seizoen niet gevonden");
        }
        $periode = $this->getDoctrine()->getRepository("CyclearGameBundle:Periode")->getCurrentPeriode();

        return array('periode' => $periode);
    }

}
