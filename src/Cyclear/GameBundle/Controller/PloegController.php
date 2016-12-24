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

use Cyclear\GameBundle\Entity\Ploeg;
use Cyclear\GameBundle\Entity\Seizoen;
use Cyclear\GameBundle\Entity\Transfer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;

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
     * @ParamConverter("seizoen", options={"mapping": {"seizoen": "slug"}})
     * @Template()
     */
    public function showAction(Request $request, Seizoen $seizoen, Ploeg $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $id;
        if (null === $entity) {
            throw $this->createNotFoundException('Unable to find Ploeg entity.');
        }
        $ploegRepo = $em->getRepository('CyclearGameBundle:Ploeg');
        $renners = $ploegRepo->getRennersWithPunten($entity);
        $uitslagRepo = $em->getRepository('CyclearGameBundle:Uitslag');
        $paginator = $this->get('knp_paginator');

        $uitslagen = $paginator->paginate(
            $uitslagRepo->getUitslagenForPloegQb($entity, $seizoen)->getQuery()->getResult(), $request->query->get('page', 1), 20
        );
        $transfers = $paginator->paginate($em->getRepository("CyclearGameBundle:Transfer")->getLatest(
            $seizoen, array(Transfer::ADMINTRANSFER, Transfer::USERTRANSFER), 9999, $entity), $request->query->get('transferPage', 1), 20, array('pageParameterName' => 'transferPage'));
        $transferUitslagen = $paginator->paginate(
            $uitslagRepo->getUitslagenForPloegForNonDraftTransfersQb($entity, $seizoen)->getQuery()->getResult(), $request->query->get('transferResultsPage', 1), 20, array('pageParameterName' => 'transferResultsPage')
        );
        $lostDrafts = $paginator->paginate(
            $uitslagRepo->getUitslagenForPloegForLostDraftsQb($entity, $seizoen)->getQuery()->getResult(), $request->query->get('page', 1), 20
        );
        $zeges = $paginator->paginate(
            $uitslagRepo->getUitslagenForPloegByPositionQb($entity, 1, $seizoen)->getQuery()->getResult(), $request->query->get('zegeResultsPage', 1), 20, array('pageParameterName' => 'zegeResultsPage')
        );

        $rennerRepo = $em->getRepository("CyclearGameBundle:Renner");
        $punten = $uitslagRepo->getPuntenByPloeg($seizoen, $entity);
        $draftRenners = $ploegRepo->getDraftRennersWithPunten($entity, false);
        $draftPunten = $uitslagRepo->getPuntenByPloegForDraftTransfers($seizoen, $entity);

        $form = $this->createFormBuilder($entity)
            ->add('memo', null, ['attr' => ['placeholder' => '...', 'rows' => 16]])
            ->add('save', SubmitType::class)
            ->getForm();
        if ('POST' === $request->getMethod()) {
            if ($form->handleRequest($request)->isValid()) {
                $em->flush($entity);
                return $this->redirect($this->generateUrl('ploeg_show', ['id' => $entity->getId(), 'seizoen' => $seizoen->getSlug()]));
            }
        }

        return array(
            'entity' => $entity,
            'renners' => $renners,
            'uitslagen' => $uitslagen,
            'seizoen' => $seizoen,
            'transfers' => $transfers,
            'rennerRepo' => $rennerRepo,
            'transferUitslagen' => $transferUitslagen,
            'lostDrafts' => $lostDrafts,
            'zeges' => $zeges,
            'punten' => $punten[0]['punten'],
            'draftRenners' => $draftRenners,
            'draftPunten' => $draftPunten[0]['punten'],
            'form' => $form->createView()
        );
    }
}
