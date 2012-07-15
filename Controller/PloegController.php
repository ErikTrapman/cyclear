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
class PloegController extends Controller {

    /**
     * Finds and displays a Ploeg entity.
     *
     * @Route("/{id}/show", name="ploeg_show")
     * @Template()
     */
    public function showAction($id) {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('CyclearGameBundle:Ploeg')->find($id);
        if (null === $entity) {
            throw $this->createNotFoundException('Unable to find Ploeg entity.');
        }

        //$renners = $entity->getRenners();
        $renners = $em->getRepository('CyclearGameBundle:Ploeg')->getRennersWithPunten($entity);
        return array(
            'entity' => $entity,
            'renners' => $renners);
    }

}
