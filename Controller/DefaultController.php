<?php

namespace Cyclear\GameBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 *
 * @Route("/game/{seizoen}")
 */
class DefaultController extends Controller
{

    /**
     * @Route("/", name="game")
     * @Template()
     */
    public function indexAction($seizoen = null)
    {

        if (null === $seizoen) {
            $seizoen = $this->getDoctrine()->getRepository("CyclearGameBundle:Seizoen")->getCurrent();
            return new \Symfony\Component\HttpFoundation\RedirectResponse($this->generateUrl("game", array("seizoen" => $seizoen->getSlug())));
        }

        $seizoen = $this->getDoctrine()->getRepository("CyclearGameBundle:Seizoen")->findBySlug($seizoen);
        if (null === $seizoen) {
            throw new \Doctrine\ORM\EntityNotFoundException("Seizoen niet gevonden");
        }
        $periode = $this->getDoctrine()->getRepository("CyclearGameBundle:Periode")->getCurrentPeriode();

        $nieuws = $this->getDoctrine()->getRepository("CyclearGameBundle:Nieuws")->findBy(array('seizoen' => $seizoen[0]), array('id' => 'DESC'), 1);
        if (!array_key_exists(0, $nieuws)) {
            $nieuws = null;
        } else {
            $nieuws = $nieuws[0];
        }
        return array('periode' => $periode, 'seizoen' => $seizoen[0], 'nieuws' => $nieuws);
    }
}
