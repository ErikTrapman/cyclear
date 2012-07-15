<?php

namespace Cyclear\GameBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 *
 * @Route("/")
 */
class DefaultController extends Controller {

    /**
     * @Route("/")
     * @Template()
     */
    public function indexAction() {

        $periode = $this->getDoctrine()->getRepository("CyclearGameBundle:Periode")->getCurrentPeriode();

        return array('periode' => $periode);
    }

}
