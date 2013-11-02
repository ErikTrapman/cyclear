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
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\View\View;
use JMS\Serializer\SerializationContext;

/**
 * @RouteResource("Rider")
 */
class RiderController extends FOSRestController
{

    /**
     * 
     * @QueryParam(name="page", requirements="\d+", strict=true, nullable=true, description="Page")
     * @QueryParam(name="query", strict=true, nullable=true, description="Query")
     * 
     */
    public function cgetAction(ParamFetcher $paramFetcher)
    {
        $em = $this->getDoctrine()->getManager();
        $paginator = $this->get('knp_paginator');
        $qb = $em->getRepository("CyclearGameBundle:Renner")->createQueryBuilder('r')->orderBy('r.naam', 'ASC');
        $view = View::create();
        $urlQuery = $paramFetcher->get('query');
        if (strlen($urlQuery) > 0) {
            if (is_numeric($urlQuery)) {
                $qb->where('r.cqranking_id = :identifier');
                $qb->setParameter('identifier', (int) $urlQuery);
            } else {
                $qb->where($qb->expr()->orx($qb->expr()->like('r.naam', ":naam")));
                $qb->setParameter('naam', "%".$urlQuery."%");
            }
        }
        $entities = $paginator->paginate(
            $qb, $paramFetcher->get('page') !== null ? $paramFetcher->get('page') : 1, 999
        );
        $view->setData($entities->getItems())->setSerializationContext(SerializationContext::create()->setGroups(array('small')));
        return $this->handleView($view);
    }
}
