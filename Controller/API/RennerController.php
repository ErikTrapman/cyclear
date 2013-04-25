<?php

namespace Cyclear\GameBundle\Controller\API;

use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\RouteResource;

/**
 * @RouteResource("Renner")
 */
class RennerController extends \FOS\RestBundle\Controller\FOSRestController
{

    /**
     * 
     */
    public function cgetAction()
    {
        $em = $this->getDoctrine()->getManager();

        $renners = $em->getRepository("CyclearGameBundle:Renner")->findAll();
        
        $view = $this->view(array($renners[0], $renners[1]), 200)->setTemplateVar('data');

        return $this->handleView($view);
    }
}
