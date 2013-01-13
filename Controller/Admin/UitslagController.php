<?php

namespace Cyclear\GameBundle\Controller\Admin;

use Cyclear\GameBundle\Entity\Uitslag;
use Cyclear\GameBundle\Entity\Wedstrijd;
use Cyclear\GameBundle\EntityManager\RennerManager;
use Cyclear\GameBundle\EntityManager\UitslagManager;
use Cyclear\GameBundle\Form\UitslagConfirmType;
use Cyclear\GameBundle\Form\UitslagNewType;
use Cyclear\GameBundle\Form\UitslagType;
use Cyclear\GameBundle\Form\WedstrijdType;
use DateTime;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use stdClass;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 *
 * @Route("admin/uitslag")
 *
 */
class UitslagController extends Controller
{

    /**
     * @Route("/", name="admin_uitslag")
     * @Template("CyclearGameBundle:Uitslag/Admin:index.html.twig")
     */
    public function indexAction()
    {

        $em = $this->getDoctrine()->getEntityManager();

        $query = $em->createQuery('SELECT w FROM CyclearGameBundle:Uitslag w ORDER BY w.id DESC');

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query, $this->get('request')->query->get('page', 1)/* page number */, 10/* limit per page */
        );
        return compact('pagination');
    }

    /**
     * @Route("/{uitslag}/edit", name="admin_uitslag_edit")
     * @Template("CyclearGameBundle:Uitslag/Admin:edit.html.twig")
     */
    public function editAction(Request $request, Uitslag $uitslag)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $seizoen = $uitslag->getSeizoen();
        $form = $this->createForm(new UitslagType(), $uitslag, array('seizoen'=>$seizoen));
        if ('POST' === $request->getMethod()) {
            $form->bind($request);
            if ($form->isValid()) {
                $em->flush();
                return $this->redirect($this->generateUrl('admin_uitslag_edit', array('uitslag'=>$uitslag->getId())));
            }
        }

        return array('form' => $form->createView(), 'entity' => $uitslag);
    }

    /**
     * Displays a form to create a new Periode entity.
     *
     * @Route("/new", name="admin_uitslag_new")
     * @Template("CyclearGameBundle:Uitslag/Admin:new.html.twig")
     */
    public function newAction()
    {
        $stdClass = new stdClass();
        $stdClass->datum = new DateTime('now');
        $form = $this->createForm(new UitslagNewType(), $stdClass);

        return array(
            'form' => $form->createView()
        );
    }

    /**
     * @Route("/prepare", name="admin_uitslag_prepare")
     * @Template("CyclearGameBundle:Uitslag/Admin:prepare.html.twig")
     * @Method("post")
     */
    public function prepareAction()
    {
        $entities = null;
        $request = $this->getRequest();
        $form = $this->createForm(new UitslagNewType());
        $form->bindRequest($request);
        if(!$form->get('url')->getData() && !$form->get('cq_wedstrijdid')->getData()){
            $this->get('session')->getFlashBag()->add('error', 'Er is iets fout gegaan. Vul de url of de CQ-id in');
            return $this->forward("CyclearGameBundle:Admin/Uitslag:new");
        }
        if ($form->isValid()) {

            $url = $form->get('url')->getData();

            $uitslagManager = $this->get('cyclear_game.manager.uitslag');
            $wedstrijdManager = $this->get('cyclear_game.manager.wedstrijd');

            $datum = $form->get('datum')->getData();
            $datum->setTime(11,0,0);
            // TODO fixme data-transformer ofzo? Ook in prepare-uitslagen gebeurt onderstaande...
            if (!$url) {
                $url = $this->container->getParameter('cq_ranking-wedstrijdurl').$form->get('cq_wedstrijdid')->getData();
            }
            $crawlerMaker = $this->get('cyclear_cqparser.crawler_manager');
            $crawler = $crawlerMaker->getCrawler($url);
            $wedstrijd = $wedstrijdManager->createWedstrijdFromCrawler($crawler, $datum);
            $wedstrijd->setSeizoen($form->get('seizoen')->getData());
            $wedstrijd->setUitslagType($form->get('uitslagtype')->getData());
            $refWedstrijd = $form->get('refentiewedstrijd')->getData();
            $puntenRefDatum = null;
            if(null !== $refWedstrijd){
                $puntenRefDatum = clone $refWedstrijd->getDatum();
            }
            $uitslagen = $uitslagManager->prepareUitslagen($form, $crawler, $wedstrijd, $puntenRefDatum);
            $confirmForm = $this->createForm(new UitslagConfirmType(), array('wedstrijd' => $wedstrijd, 'uitslag' => $uitslagen, 'registry' => $this->get('doctrine')));

            return( array('form' => $confirmForm->createView()) );
        }
        return array(
            'entity' => null,
            'form' => $form->createView()
        );
    }

    /**
     * @Route("/confirm", name="admin_uitslag_confirm")
     * @Method("post")
     */
    public function confirmAction()
    {
        $rawData = $this->getRequest()->get('cyclear_gamebundle_uitslagconfirmtype');
        $em = $this->getDoctrine()->getEntityManager();
        
        $confirmForm = $this->createForm(new UitslagConfirmType(), array('registry'=>$this->getDoctrine()));
        $confirmForm->bind($this->getRequest());
        $wedstrijd = $confirmForm->get('wedstrijd')->getData();
        $em->persist($wedstrijd);
        $rawUitslagen = $rawData['uitslag'];
        foreach($confirmForm->get('uitslag')->getData() as $key => $uitslag){
            // de wedstrijd en seizoen staan niet in het formulier
            $uitslag->setWedstrijd($wedstrijd);
            // TODO wedstrijd heeft al een seizoen, uitslag zou er geen hoeven hebben
            $uitslag->setSeizoen($wedstrijd->getSeizoen());
            // we geven alsnog de mogelijkheid om een renner aan te passen. mss staat die nog niet in de db.
            if(null === $uitslag->getRenner()){
                $manager = new RennerManager($em);
                $renner = $manager->createRennerFromRennerSelectorTypeString($rawUitslagen[$key]['renner']);
                $em->persist($renner);
                $uitslag->setRenner($renner);
            }
            $em->persist($uitslag);
        }
        $em->flush();
        return $this->redirect($this->generateUrl('admin_uitslag'));
    }
}