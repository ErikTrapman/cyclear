<?php

/*
 * This file is part of the Cyclear-game package.
 *
 * (c) Erik Trapman <veggatron@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller;

use App\Entity\Seizoen;
use App\Entity\UitslagRepository;
use App\Entity\Wedstrijd;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/{seizoen}/wedstrijd")
 */
class WedstrijdController extends Controller
{

    /**
     * @Route("/latest", name="wedstrijd_latest")
     * @ParamConverter("seizoen", options={"mapping": {"seizoen": "slug"}})
     * @Template()
     */
    public function latestAction(Request $request, Seizoen $seizoen)
    {
        $em = $this->getDoctrine()->getManager();
        $uitslagenQb = $em->getRepository(Wedstrijd::class)->createQueryBuilder('w')
            ->where('w.seizoen = :seizoen')->setParameter('seizoen', $seizoen)
            ->orderBy('w.datum', 'DESC')
            ->setMaxResults(20);
        return array('wedstrijden' => $uitslagenQb->getQuery()->getResult(), 'seizoen' => $seizoen);
    }

    /**
     * @Route("/{wedstrijd}", name="wedstrijd_show")
     * @Template()
     */
    public function showAction(Request $request, Wedstrijd $wedstrijd)
    {
        $em = $this->getDoctrine()->getManager();
        $refStages = $em->getRepository(Wedstrijd::class)->getRefStages($wedstrijd, $wedstrijd);
        $allStages = [];
        /** @var Wedstrijd $refStage */
        foreach (array_merge($refStages, [$wedstrijd]) as $refStage) {
            foreach ($refStage->getUitslagenGrouped(true) as $team => $resultInfo) {
                if (array_key_exists($team, $allStages)) {
                    $allStages[$team]['total'] += $resultInfo['total'];
                    $allStages[$team]['hits'] += $resultInfo['hits'];
                    $allStages[$team]['renners'] = array_merge($allStages[$team]['renners'], $resultInfo['renners']);
                } else {
                    $allStages[$team] = $resultInfo;
                }
            }
        }
        UitslagRepository::puntenSort($allStages, 'hits', 'total');

        return [
            'wedstrijd' => $wedstrijd,
            'uitslagen' => array_merge($refStages, [$wedstrijd]),
            'allstages' => $allStages
        ];
    }

    /**
     * @Route("en", name="wedstrijd_list")
     * @ParamConverter("seizoen", options={"mapping": {"seizoen": "slug"}})
     * @Template()
     */
    public function indexAction(Request $request, Seizoen $seizoen)
    {
        $em = $this->get('doctrine.orm.default_entity_manager');

        $qb = $em->getRepository(Wedstrijd::class)->createQueryBuilder('n')
            ->where('n.seizoen = :seizoen')->setParameter('seizoen', $seizoen)
            ->orderBy('n.id', 'DESC');

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $qb, $request->query->get('page', 1), 20
        );
        return array('pagination' => $pagination, 'seizoen' => $seizoen);
    }
}
