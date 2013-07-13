<?php

namespace Cyclear\GameBundle\Controller\API;

use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use JMS\Serializer\SerializationContext;

/**
 * @RouteResource("Team")
 */
class TeamController extends \FOS\RestBundle\Controller\FOSRestController
{

    public function cgetAction($seizoen)
    {
        $em = $this->getDoctrine()->getManager();
        $seizoen = $em->getRepository("CyclearGameBundle:Seizoen")->findOneBySlug($seizoen);

        $ploegen = $em->getRepository("CyclearGameBundle:Ploeg")
                ->createQueryBuilder('p')
                ->where('p.seizoen =:seizoen')
                ->orderBy('p.afkorting')
                ->setParameter('seizoen', $seizoen)
                ->getQuery()->getResult();
        $map = array();
        foreach ($em->getRepository("CyclearGameBundle:Uitslag")->getPuntenByPloeg($seizoen) as $result) {
            $map[$result['id']] = $result['punten'];
        }
        foreach ($ploegen as $ploeg) {
            $ploeg->setPunten($map[$ploeg->getId()]);
        }
        $view = $this->view($ploegen, 200)->setSerializationContext(SerializationContext::create()->setGroups(array('medium')));
        return $this->handleView($view);
    }

    public function getAction($seizoen, $teamslug)
    {
        $em = $this->getDoctrine()->getManager();
        $seizoen = $em->getRepository("CyclearGameBundle:Seizoen")->findOneBySlug($seizoen);
        $ploeg = $em->getRepository("CyclearGameBundle:Ploeg")->findOneByAfkorting($teamslug);
        $punten = $em->getRepository("CyclearGameBundle:Uitslag")->getPuntenByPloeg($seizoen, $ploeg);
        $ploeg->setPunten($punten[0]['punten']);
        $view = $this->view($ploeg, 200)->setSerializationContext(SerializationContext::create()->setGroups(array('medium')));
        return $this->handleView($view);
    }
}