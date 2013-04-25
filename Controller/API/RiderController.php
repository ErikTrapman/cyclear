<?php

namespace Cyclear\GameBundle\Controller\API;

use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\RouteResource;

/**
 * @RouteResource("Rider")
 */
class RiderController extends \FOS\RestBundle\Controller\FOSRestController
{

    /**
     * 
     * @QueryParam(name="page", requirements="\d+", strict=true, nullable=true, description="Page")
     * 
     */
    public function cgetAction(\FOS\RestBundle\Request\ParamFetcher $paramFetcher)
    {
        $em = $this->getDoctrine()->getManager();

        $paginator = $this->get('knp_paginator');
        $query = $em->createQuery("SELECT r FROM CyclearGameBundle:Renner r ORDER BY r.naam ASC");
        
        $entities = $paginator->paginate(
            $query, $paramFetcher->get('page'), 20
        );
        $view = $this->view($entities->getItems(), 200)->setTemplateVar('data');

        return $this->handleView($view);
    }
}
