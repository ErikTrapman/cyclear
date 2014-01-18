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

use Doctrine\ORM\NoResultException;
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
        usort($ploegen, function ($a, $b) {
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

    public function getPointsAction($seizoen, $team)
    {
        $em = $this->getDoctrine()->getManager();
        $seizoen = $em->getRepository("CyclearGameBundle:Seizoen")->findOneBySlug($seizoen);
        $ploeg = $em->getRepository("CyclearGameBundle:Ploeg")->find($team);

        $qb = $em->getRepository("CyclearGameBundle:Uitslag")
            ->createQueryBuilder('u')
            ->innerJoin('u.wedstrijd', 'w')
            ->where('u.ploeg = :ploeg')
            ->andWhere('w.seizoen = :seizoen')
            ->addSelect('SUM(u.ploegPunten) AS ttl')
            ->addSelect('DATE_FORMAT(w.datum, \'%Y%m%d\') AS groupdate')
            ->addSelect('w')
            ->groupBy('groupdate')->setParameters(array('seizoen' => $seizoen, 'ploeg' => $ploeg));

        //echo $qb->getDQL();die;
        $res = array();
        foreach ($qb->getQuery()->getArrayResult() as $row) {
            $res[$row['groupdate']] = $row['ttl'];
        }
        try {
            $start = $em
                ->createQuery(sprintf("SELECT MIN(w.datum) AS maxdate FROM CyclearGameBundle:Wedstrijd w WHERE w.seizoen = %d", $seizoen->getId()))
                ->getSingleScalarResult();
            $start = new \DateTime($start);
            $start->modify("-1 day");
        } catch (NoResultException $e) {
            $start = new \DateTime(date('Y'), 1, 1);
            $start->setTime(0, 0, 0);
        }

        try {
            $possibleEndDate = $em
                ->createQuery(sprintf("SELECT MAX(w.datum) AS maxdate FROM CyclearGameBundle:Wedstrijd w WHERE w.seizoen = %d", $seizoen->getId()))
                ->getSingleScalarResult();
            $possibleEndDate = new \DateTime($possibleEndDate);
            $possibleEndDate->modify("+1 week");
        } catch (NoResultException $e) {
            $possibleEndDate = clone $start;
        }
        $end = clone $start;
        $end->modify("+1 week");
        if ($possibleEndDate > $end) {
            $end = $possibleEndDate;
        }

        $ret = array();
        while ($start < $end) {
            $key = $start->format('Ymd');
            $ret[$key] = 0;
            $start->modify("+1 day");
        }

        $ttl = 0;
        foreach ($ret as $key => $value) {
            if (array_key_exists($key, $res)) {
                $ttl += $res[$key];
            }
            $ret[$key] = $ttl;
        }

        $view = $this->view($ret, 200);
        return $this->handleView($view);
    }
}