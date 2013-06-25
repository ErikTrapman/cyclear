<?php

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
        $seizoen = $this->getDoctrine()->getRepository("CyclearGameBundle:Seizoen")->findBySlug($seizoen);
        $renners = $em->getRepository('CyclearGameBundle:Ploeg')->getRennersWithPunten($entity);
        // TODO repository function maken
        $uitslagenQb = $em->getRepository('CyclearGameBundle:Uitslag')
                ->createQueryBuilder("u")
                ->join('u.wedstrijd', 'w')
                ->where('w.seizoen = :seizoen')->andWhere('u.ploeg = :ploeg')->andWhere('u.ploegPunten > 0')
                ->setParameters(array("seizoen" => $seizoen[0], "ploeg" => $entity))
                ->orderBy("u.renner")->orderBy('u.id', 'DESC')
        ;
        $paginator = $this->get('knp_paginator');
        $uitslagen = $paginator->paginate(
            $uitslagenQb->getQuery()->getResult(), $this->get('request')->query->get('page', 1)/* page number */, 10/* limit per page */
        );
        //var_dump($uitslagen);
        $transferPaginator = $this->get('knp_paginator');
        $transfers = $em->getRepository("CyclearGameBundle:Transfer")->getLatest(
            $seizoen[0], array(Transfer::ADMINTRANSFER, Transfer::USERTRANSFER), 9999, $entity);
        $transfers = $transferPaginator->paginate($transfers,
            $this->get('request')->query->get('transferPage', 1), 10, array('pageParameterName' => 'transferPage'));
        $rennerRepo = $em->getRepository("CyclearGameBundle:Renner");
        return array(
            'entity' => $entity,
            'renners' => $renners,
            'uitslagen' => $uitslagen,
            'seizoen' => $seizoen[0],
            'transfers' => $transfers,
            'rennerRepo' => $rennerRepo
            );
    }
}
