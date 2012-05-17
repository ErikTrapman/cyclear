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
 * @Route("/ploeg")
 */
class PloegController extends Controller
{
    /**
     * Lists all Ploeg entities.
     *
     * @Route("/", name="ploeg")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entities = $em->getRepository('CyclearGameBundle:Ploeg')->findAll();

        return array('entities' => $entities);
    }

    /**
     * Finds and displays a Ploeg entity.
     *
     * @Route("/{id}/show", name="ploeg_show")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('CyclearGameBundle:Ploeg')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Ploeg entity.');
        }
		
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
        	'renners'	=> $entity->getRenners(),
            'delete_form' => $deleteForm->createView(),        );
    }


}
