<?php

namespace Cyclear\GameBundle\Controller\API;

use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\RouteResource;

/**
 * @RouteResource("Team")
 */
class TeamController extends \FOS\RestBundle\Controller\FOSRestController
{

    public function cgetAction($slug)
    {
        $em = $this->getDoctrine()->getManager();
        $seizoen = $em->getRepository("CyclearGameBundle:Seizoen")->findOneBySlug($slug);

        $ploegen = $em->getRepository("CyclearGameBundle:Ploeg")
                ->createQueryBuilder('p')
                ->where('p.seizoen =:seizoen')
                ->orderBy('p.afkorting')
                ->setParameter('seizoen', $seizoen)
                ->getQuery()->getResult();
        $map = array();
        foreach($em->getRepository("CyclearGameBundle:Uitslag")->getPuntenByPloeg($seizoen) as $result){
            $map[$result['id']] = $result['punten'];
        }
        foreach($ploegen as $ploeg){
            $ploeg->setPunten($map[$ploeg->getId()]);
        }
        $view = $this->view($ploegen, 200);
        return $this->handleView($view);
    }

    public function getAction($slug, $teamslug)
    {
        
    }
}