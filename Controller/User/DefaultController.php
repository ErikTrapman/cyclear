<?php

namespace Cyclear\GameBundle\Controller\User;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 *
 * @Route("/user")
 */
class DefaultController extends Controller
{
    /**
     * @Route("/")
     * @Template("CyclearGameBundle:Default/User:index.html.twig")
     */
    public function indexAction()
    {
    	return array();
    }
}
