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
        // sort
        usort($ploegen, function($a, $b) {
                if ($a->getPunten() == $b->getPunten()) {
                    return 0;
                }
                return ($a->getPunten() < $b->getPunten()) ? 1 : -1;
            });
        $view = $this->view($ploegen, 200)->setSerializationContext(SerializationContext::create()->setGroups(array('medium')));
        return $this->handleView($view);
    }

    public function getTeamAction($seizoen, $teamslug)
    {
        $em = $this->getDoctrine()->getManager();
        $seizoen = $em->getRepository("CyclearGameBundle:Seizoen")->findOneBySlug($seizoen);
        $ploeg = $em->getRepository("CyclearGameBundle:Ploeg")->findOneByAfkorting($teamslug);
        $punten = $em->getRepository("CyclearGameBundle:Uitslag")->getPuntenByPloeg($seizoen, $ploeg);
        $ploeg->setPunten($punten[0]['punten']);
        $view = $this->view($ploeg, 200)->setSerializationContext(SerializationContext::create()->setGroups(array('medium')));
        return $this->handleView($view);
    }

    public function cgetPointsAction($seizoen)
    {
        $start = new \DateTime('2013-01-01');
        $end = new \DateTime('2013-12-31');
//        $ret = array();
//        do {
//            $ret[$start->format('Ymd')] = array();
//            for ($i = 1; $i <= 5; $i++) {
//                $ret[$start->format('Ymd')][$i] = rand(10, 433);
//            }
//            $start->modify("+1 month");
//        } while($start < $end);
        
        $ret = array();
        $loopStart = clone $start;
        $loopEnd = clone $end;
        for ($i = 1; $i <= 5; $i++) {
            $points = 0;
            $ret[$i] = array();
            $loopStart = clone $start;
            $loopEnd = clone $end;
            do {
                $ret[$i][] = array('date' => $loopStart->format('Ymd'), 'points' => $points += rand(10, 433));
                $loopStart->modify('+1 month');
            } while ($loopStart < $loopEnd);
        }
        $view = $this->view($ret, 200);
        return $this->handleView($view);
    }
}