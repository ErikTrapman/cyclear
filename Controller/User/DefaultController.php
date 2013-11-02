<?php

namespace Cyclear\GameBundle\Controller\User;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 *
 * @Route("/user/{seizoen}")
 */
class DefaultController extends Controller
{
    /**
     * @Route("/")
     * @Template("CyclearGameBundle:Default/User:index.html.twig")
     */
    public function indexAction($seizoen)
    {
    	$seizoen = $this->getDoctrine()->getRepository("CyclearGameBundle:Seizoen")->findBySlug($seizoen);
        return array('seizoen' => $seizoen[0]);
    }
}
