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
        $stmt = $em->getConnection()->prepare("SELECT DATE_FORMAT(w.datum,'%Y-01-01 00:00:00') AS mindate 
            FROM Wedstrijd w WHERE w.seizoen_id = :seizoen ORDER BY w.datum ASC LIMIT 1");
        $stmt->execute($params);
        $minDate = $stmt->fetchAll();
        $start = \DateTime::createFromFormat('Y-m-d 00:00:00', !empty($minDate) ? $minDate[0]['mindate'] : date('Y-01-01 00:00:00'));
        // end-date is the end of the year

        $stmt = $em->getConnection()->prepare("SELECT DATE_FORMAT(w.datum,'%Y-%m-%d 00:00:00') AS maxdate 
            FROM Wedstrijd w WHERE w.seizoen_id = :seizoen ORDER BY w.datum DESC LIMIT 1");
        $stmt->execute($params);
        $maxDate = $stmt->fetchAll();
        $end = \DateTime::createFromFormat('Y-m-d 00:00:00', !empty($maxDate) ? $maxDate[0]['maxdate'] : date('Y-12-31 00:00:00'));
        $checkDate = clone $start;
        $checkDate->modify("+1 month");
        if ($end < $checkDate) {
            $end = $checkDate;
        } else if ($end < new \DateTime()) {
            $end = new \DateTime();
            $end->setTime(0, 0, 0);
        }
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