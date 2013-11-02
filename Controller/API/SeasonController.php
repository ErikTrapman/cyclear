<?php

/*
 * This file is part of the Cyclear-game package.
 *
 * (c) Erik Trapman <veggatron@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cyclear\GameBundle\Controller\API;

use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\RouteResource;

/**
 * @RouteResource("Season")
 */
class SeasonController extends \FOS\RestBundle\Controller\FOSRestController
{

    public function getAction($seizoen)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository("CyclearGameBundle:Seizoen")->findOneBySlug($seizoen);
        
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