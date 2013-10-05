<?php

namespace Cyclear\GameBundle\Controller\API;

use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\View\View;
use JMS\Serializer\SerializationContext;

/**
 * @RouteResource("Rider")
 */
class SeasonRiderController extends FOSRestController
{
    
    /**
     * 
     * @QueryParam(name="page", requirements="\d+", strict=true, nullable=true, description="Page")
     * @QueryParam(name="query", strict=true, nullable=true, description="Query")
     * 
     */
    public function cgetAction($seizoen, ParamFetcher $paramFetcher)
    {
        $em = $this->getDoctrine()->getManager();
        $paginator = $this->get('knp_paginator');
        $qb = $em->getRepository("CyclearGameBundle:Renner")->createQueryBuilder('r')->orderBy('r.naam', 'ASC');
        $request = $this->getRequest();
        $view = View::create();
        $urlQuery = $paramFetcher->get('query');
        if (strlen($urlQuery) > 0) {
            $qb->where($qb->expr()->orx($qb->expr()->like('r.naam', ":naam")));
            $qb->setParameter('naam', "%".$urlQuery."%");
        }
        $entities = $paginator->paginate(
            $qb, $paramFetcher->get('page') !== null ? $paramFetcher->get('page') : 1, 20
        );
        if ('html' === $request->getRequestFormat()) {
            $seizoen = $em->getRepository("CyclearGameBundle:Seizoen")->findOneBySlug($seizoen);
            $listWithPunten = $this->getDoctrine()->getRepository("CyclearGameBundle:Uitslag")->getPuntenWithRenners($seizoen, 20);
            $listWithPuntenNoPloeg = $this->getDoctrine()->getRepository("CyclearGameBundle:Uitslag")->getPuntenWithRennersNoPloeg($seizoen, 20);
            $view->setTemplate("CyclearGameBundle:API/Season/Rider:riders.html.twig");
            $view->setData(
                array(
                    'entities' => $entities,
                    'with_team' => $listWithPunten,
                    'without_team' => $listWithPuntenNoPloeg,
                    'rennerRepo' => $this->getDoctrine()->getRepository("CyclearGameBundle:Renner"),
                    'seizoen' => $seizoen
                )
            );
            return $this->handleView($view);
        }
        $view->setData($entities->getItems())->setSerializationContext(SerializationContext::create()->setGroups(array('medium')));
        return $this->handleView($view);
    }

    
    
    public function getAction($seizoen, $riderslug)
    {
        
    }
}