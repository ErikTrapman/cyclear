<?php

namespace Cyclear\GameBundle\Controller;

use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Cyclear\GameBundle\Entity\Ploeg;
use Cyclear\GameBundle\Form\PloegType;

/**
 * Ploeg controller.
 *
 * @Route("/game/{seizoen}/renner")
 */
class RennerController extends Controller
{

    /**
     * @Route("/", name="renner_index")
     * @Template("CyclearGameBundle:Renner:index.html.twig")
     */
    public function indexAction($seizoen)
    {
//        $filter = $this->createForm('renner_filter');
//        $em = $this->getDoctrine();//->getEntityManager();
//
//        $config = $em->getConfiguration();
//        $config->addFilter("naam", "Cyclear\GameBundle\Filter\RennerNaamFilter");
//
//        if ($this->getRequest()->getMethod() == 'POST') {
//            $filter->bindRequest($this->getRequest());
//            if ($filter->isValid()) {
//                if ($filter->get('naam')->getData()) {
//                    $em->getFilters()->enable("naam")->setParameter("naam", $filter->get('naam')->getData());
//                }
//            }
//        }
//        $q='SELECT r FROM CyclearGameBundle:Rennerr r ORDER BY r.naam ASC';
//        $query = $this->getEntityManager()->createQuery($q);
//        /*
//        $query = $em->getRepository("CyclearGameBundle:Renner")
//            ->createQueryBuilder('r')
//            ->orderBy('RAND()')
//            ->getQuery();
//        $entities = $query->getResult();
//        */
//        $paginator = $this->get('knp_paginator');
//        $pagination = $paginator->paginate(
//            $query, $this->get('request')->query->get('page', 1)/* page number */, 10/* limit per page */
//        );
//
//
//        $seizoen = $this->getDoctrine()->getRepository("CyclearGameBundle:Seizoen")->findBySlug($seizoen);
//        return array('pagination' => $pagination, 'filter' => $filter->createView(), 'seizoen' => $seizoen[0]);
    }

    /**
     * @Route("/punten", name="renner_punten")
     * 
     * @Template()
     */
    public function puntenAction(\Symfony\Component\HttpFoundation\Request $request)
    {
        $seizoen = $this->getDoctrine()->getRepository("CyclearGameBundle:Seizoen")->findBySlug($request->get('seizoen'));

        $filter = $this->createForm('renner_filter');
        $em = $this->getDoctrine()->getEntityManager();
        $query = $em->createQuery("SELECT r FROM Cyclear\GameBundle\Entity\Renner r ORDER BY r.naam");

        $config = $em->getConfiguration();
        $config->addFilter("naam", "Cyclear\GameBundle\Filter\RennerNaamFilter");

        if ($this->getRequest()->getMethod() == 'POST') {
            $filter->bindRequest($this->getRequest());
            if ($filter->isValid()) {
                if ($filter->get('naam')->getData()) {
                    $em->getFilters()->enable("naam")->setParameter("naam", $filter->get('naam')->getData());
                }
            }
        }
        $entities = $query->getResult();
        if (array_key_exists('naam', $em->getFilters()->getEnabledFilters())) {
            $em->getFilters()->disable('naam');
        }
        $listWithPunten = $this->getDoctrine()->getRepository("CyclearGameBundle:Uitslag")->getPuntenWithRenners($seizoen[0], 20);
        $listWithPuntenNoPloeg = $this->getDoctrine()->getRepository("CyclearGameBundle:Uitslag")->getPuntenWithRennersNoPloeg($seizoen[0], 20);

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $entities, $this->get('request')->query->get('page', 1)/* page number */, 10/* limit per page */
        );


        return array(
            'pagination' => $pagination, 'filter' => $filter->createView(),
            'listWithPunten' => $listWithPunten,
            'seizoen' => $seizoen[0],
            'rennerRepo' => $this->getDoctrine()->getRepository("CyclearGameBundle:Renner"),
            'listWithPuntenNoPloeg' => $listWithPuntenNoPloeg
        );
    }

    /**
     * @Route("/{renner}", name="renner_show")
     * @Template("CyclearGameBundle:Renner:show.html.twig")
     */
    public function showAction($seizoen, \Cyclear\GameBundle\Entity\Renner $renner)
    {
        $seizoen = $this->getDoctrine()->getRepository("CyclearGameBundle:Seizoen")->findBySlug($seizoen);
        $transfers = $this->getDoctrine()->getRepository("CyclearGameBundle:Transfer")->findByRenner($renner, $seizoen[0]);
        $transferrepo = $this->getDoctrine()->getRepository("CyclearGameBundle:Transfer");

        $uitslagen = $this->getDoctrine()->getRepository("CyclearGameBundle:Uitslag")->getPuntenForRenner($renner, $seizoen[0]);

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $uitslagen, $this->get('request')->query->get('page', 1)/* page number */, 10/* limit per page */
        );

        return array('seizoen' => $seizoen[0], 'renner' => $renner, 'transfers' => $transfers, 'uitslagen' => $pagination, 'transferrepo' => $transferrepo);
    }
}