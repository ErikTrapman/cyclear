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
        /* @type \Doctrine\ORM\EntityManager */
        $em = $this->getDoctrine()->getManager();
        $seizoen = $em->getRepository("CyclearGameBundle:Seizoen")->findOneBySlug($seizoen);
        $params = array('seizoen' => $seizoen->getId());
        $stmt = $em->getConnection()->prepare("
            SELECT SUM(u.ploegPunten) AS points, u.ploeg_id, DATE_FORMAT(w.datum,'%Y%m') AS date
            FROM Uitslag u 
                INNER JOIN Wedstrijd w ON u.wedstrijd_id = w.id
                WHERE w.seizoen_id = :seizoen AND u.ploeg_id IS NOT NULL
                GROUP BY u.ploeg_id, date
                ORDER BY u.ploeg_id, date");
        $stmt->execute($params);
        $map = array();
        $teampoints = array();
        foreach ($stmt->fetchAll() as $row) {
            $teampoints[$row['ploeg_id']] = ( array_key_exists($row['ploeg_id'], $teampoints)) ? $teampoints[$row['ploeg_id']] + $row['points'] : $row['points'];
            $map[$row['ploeg_id']][$row['date']] = array('date' => $row['date'], 'points' => $teampoints[$row['ploeg_id']]);
        }
        // start-date
        $start = new \DateTime();
        $start->setDate(date('Y'),1,1)->setDate(0,0,0);
        // end-date is the end of the year
        $end = new \DateTime();
        $end->setDate(date('Y'),12,31)->setTime(23,59,59);
        // prepare all months
        $months = array();
        do {
            $months[] = clone $start;
            $start->modify("+1 month");
        } while ($start < $end);
        $ret = array();
        $now = new \DateTime();
        $now->modify('first day of this month');

        foreach ($em->getRepository("CyclearGameBundle:Ploeg")->createQueryBuilder('t')->where('t.seizoen = :seizoen')
            ->setParameter('seizoen', $seizoen)->getQuery()->getResult() as $team) {
            $mapData = (array_key_exists($team->getId(), $map) ) ? $map[$team->getId()] : array();
            $points = 0;
            foreach ($months as $month) {
                $monthFormat = $month->format('Ym');
                if (array_key_exists($monthFormat, $mapData) && $month <= $now) {
                    $points = $mapData[$monthFormat]['points'];
                } elseif ($month > $now) {
                    $points = null;
                }
                $ret[$team->getId()][$monthFormat] = array('date' => $monthFormat, 'points' => $points);
            }
        }
        $view = $this->view($ret, 200)->setSerializationContext(SerializationContext::create()->setSerializeNull(true));
        return $this->handleView($view);
    }
}