<?php

namespace Cyclear\GameBundle\Controller;

use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Cyclear\GameBundle\Entity\Ploeg;
use Cyclear\GameBundle\Form\PloegType;

/**
 * Ploeg controller.
 *
 * @Route("/renner")
 */
class RennerController extends Controller
{
	
	/**
     * Lists all Ploeg entities.
     *
     * @Route("/", name="renner")
     * @Template("CyclearGameBundle:Renner:index.html.twig")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entities = $em->getRepository('CyclearGameBundle:Renner')->findAll();

        return array('entities' => $entities);
    }
}