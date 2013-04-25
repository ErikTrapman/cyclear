<?php

namespace Cyclear\GameBundle\Controller\API;

use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\RouteResource;

/**
 * @RouteResource("Season")
 */
class SeasonController extends \FOS\RestBundle\Controller\FOSRestController
{

    public function getAction($slug)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository("CyclearGameBundle:Seizoen")->findOneBySlug($slug);
        
        $view = $this->view($entity, 200)->setTemplateVar('data');

        return $this->handleView($view);
        
    }

    public function getCurrentAction()
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository("CyclearGameBundle:Seizoen")->findOneByCurrent(1);
        
        $view = $this->view($entity, 200)->setTemplateVar('data');

        return $this->handleView($view);
    }
}