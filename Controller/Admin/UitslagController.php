<?php

namespace Cyclear\GameBundle\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Cyclear\GameBundle\Form\UitslagNewType,
    Cyclear\GameBundle\Form\UitslagConfirmType,
    Cyclear\GameBundle\EntityManager\UitslagManager;

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
    public function editAction(\Symfony\Component\HttpFoundation\Request $request, \Cyclear\GameBundle\Entity\Uitslag $uitslag)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $seizoen = $uitslag->getSeizoen();
        $form = $this->createForm(new \Cyclear\GameBundle\Form\UitslagType(), $uitslag, array('seizoen'=>$seizoen));
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
        $stdClass = new \stdClass();
        $stdClass->datum = new \DateTime('now');
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
        $form = $this->createForm(new UitslagNewType(), null);
        $form->bindRequest($request);

        if ($form->isValid()) {

            $url = $form->get('url')->getData();

            $uitslagManager = $this->get('cyclear_game.manager.uitslag');
            $wedstrijdManager = $this->get('cyclear_game.manager.wedstrijd');

            $datum = $form->get('datum')->getData();
            $datum->add(new \DateInterval("PT11H"));
            $uitslagen = $uitslagManager->prepareUitslagen($form);
            // TODO fixme data-transformer ofzo? Ook in prepare-uitslagen gebeurt onderstaande...
            if (!$url) {
                $url = $this->container->getParameter('cq_ranking-wedstrijdurl').$form->get('cq_wedstrijd-id')->getData();
            }
            $wedstrijd = $wedstrijdManager->createWedstrijdFromUrl($url, $datum);
            $wedstrijd->setSeizoen($form->get('seizoen')->getData());
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
        //$request = $this->getRequest()->get();
        $request = $this->getRequest()->get('cyclear_gamebundle_uitslagconfirmtype');
        $em = $this->getDoctrine()->getEntityManager();

        $wedstrijd = new \Cyclear\GameBundle\Entity\Wedstrijd();
        $wedstrijdForm = $this->createForm(new \Cyclear\GameBundle\Form\WedstrijdType(), $wedstrijd);
        $wedstrijdForm->bind($request['wedstrijd']);

        //$wedstrijd->setNaam( $request['wedstrijd']['naam'] );
        //$wedstrijd->setDatum( new \DateTime($request['wedstrijd']['datum']) );
        $em->persist($wedstrijd);

        $uitslagen = $request['uitslag'];
        $seizoen = $wedstrijd->getSeizoen(); // $em->getRepository("CyclearGameBundle:Seizoen")->find($request['seizoen']);
        foreach ($uitslagen as $uitslag) {
            $currentUitslag = new \Cyclear\GameBundle\Entity\Uitslag();
            $uitslagForm = $this->createForm(new \Cyclear\GameBundle\Form\UitslagType(), $currentUitslag);
            $renner = $em->getRepository('CyclearGameBundle:Renner')->findOneBySelectorString($uitslag['renner']);
            if ($renner === null) {
                $manager = new \Cyclear\GameBundle\EntityManager\RennerManager($em);
                $renner = $manager->createRennerFromRennerSelectorTypeString($uitslag['renner']);
                $em->persist($renner);
            }
            //$uitslag['renner'] = $uitslag['renner'];
            $uitslagForm->bind($uitslag);

            $currentUitslag->setRenner($renner);
            $currentUitslag->setPloeg($uitslagForm->get('ploeg')->getData());
            $currentUitslag->setSeizoen($seizoen);
            $currentUitslag->setDatum($wedstrijd->getDatum());
            $currentUitslag->setWedstrijd($wedstrijd);
            $currentUitslag->setRennerPunten($uitslag['rennerPunten']); // FIXME rennerPunten mogelijk maken
            $em->persist($currentUitslag);
        }
        $em->flush();
        return $this->redirect($this->generateUrl('admin_uitslag'));
    }
}