<?php

/*
 * This file is part of the Cyclear-game package.
 *
 * (c) Erik Trapman <veggatron@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cyclear\GameBundle\Controller;

use Cyclear\GameBundle\Entity\Transfer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Ploeg controller.
 *
 * @Route("/{seizoen}/ploeg")
 */
class PloegController extends Controller
{

    /**
     * Finds and displays a Ploeg entity.
     *
     * @Route("/{id}/show", name="ploeg_show")
     * @Template()
     */
    public function showAction($seizoen, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('CyclearGameBundle:Ploeg')->find($id);
        if (null === $entity) {
            throw $this->createNotFoundException('Unable to find Ploeg entity.');
        }
        $ploegRepo = $em->getRepository('CyclearGameBundle:Ploeg');
        $seizoen = $this->getDoctrine()->getRepository("CyclearGameBundle:Seizoen")->findBySlug($seizoen);
        $renners = $ploegRepo->getRennersWithPunten($entity);
        $uitslagRepo = $em->getRepository('CyclearGameBundle:Uitslag');
        $paginator = $this->get('knp_paginator');

        $uitslagen = $paginator->paginate(
            $uitslagRepo->getUitslagenForPloegQb($entity, $seizoen[0])->getQuery()->getResult(), $this->get('request')->query->get('page', 1)
        );
        $transfers = $paginator->paginate($em->getRepository("CyclearGameBundle:Transfer")->getLatest(
            $seizoen[0], array(Transfer::ADMINTRANSFER, Transfer::USERTRANSFER), 9999, $entity), $this->get('request')->query->get('transferPage', 1), 10, array('pageParameterName' => 'transferPage'));
        $transferUitslagen = $paginator->paginate(
            $uitslagRepo->getUitslagenForPloegForNonDraftTransfersQb($entity, $seizoen[0])->getQuery()->getResult(), $this->get('request')->query->get('transferResultsPage', 1), 10, array('pageParameterName' => 'transferResultsPage')
        );
        $lostDrafts = $paginator->paginate(
            $uitslagRepo->getUitslagenForPloegForLostDraftsQb($entity, $seizoen)->getQuery()->getResult(), $this->get('request')->query->get('page', 1)
        );
        $zeges = $paginator->paginate(
            $uitslagRepo->getUitslagenForPloegByPositionQb($entity, 1, $seizoen[0])->getQuery()->getResult(), $this->get('request')->query->get('zegeResultsPage', 1), 10, array('pageParameterName' => 'zegeResultsPage')
        );

        $rennerRepo = $em->getRepository("CyclearGameBundle:Renner");
        $punten = $uitslagRepo->getPuntenByPloeg($seizoen[0], $entity);
        $draftRenners = $ploegRepo->getDraftRennersWithPunten($entity, $seizoen[0], false);
        $draftPunten = $uitslagRepo->getPuntenByPloegForDraftTransfers($seizoen[0], $entity);
        return array(
            'entity' => $entity,
            'renners' => $renners,
            'uitslagen' => $uitslagen,
            'seizoen' => $seizoen[0],
            'transfers' => $transfers,
            'rennerRepo' => $rennerRepo,
            'transferUitslagen' => $transferUitslagen,
            'lostDrafts' => $lostDrafts,
            'zeges' => $zeges,
            'punten' => $punten[0]['punten'],
            'draftRenners' => $draftRenners,
            'draftPunten' => $draftPunten[0]['punten']
        );
    }
}
