<?php

/*
 * This file is part of the Cyclear-game package.
 *
 * (c) Erik Trapman <veggatron@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
use Symfony\Component\HttpFoundation\Response;

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

        $em = $this->getDoctrine()->getManager();

        $query = $em->createQuery('SELECT w FROM CyclearGameBundle:Uitslag w ORDER BY w.id DESC');

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query, $this->get('request')->query->get('page', 1)/* page number */, 20/* limit per page */
        );
        $seizoen = $em->getRepository("CyclearGameBundle:Seizoen")->getCurrent();
        return array('pagination' => $pagination, 'seizoen' => $seizoen);
    }

    /**
     * @Route("/{uitslag}/edit", name="admin_uitslag_edit")
     * @Template("CyclearGameBundle:Uitslag/Admin:edit.html.twig")
     */
    public function editAction(Request $request, Uitslag $uitslag)
    {
        $em = $this->getDoctrine()->getManager();
        $seizoen = $uitslag->getWedstrijd()->getSeizoen();
        $form = $this->createForm(new UitslagType(), $uitslag, array('seizoen' => $seizoen));
        if ('POST' === $request->getMethod()) {
            $form->submit($request);
            if ($form->isValid()) {
                $em->flush();
                return $this->redirect($this->generateUrl('admin_uitslag_edit', array('uitslag' => $uitslag->getId())));
            }
        }

        return array('form' => $form->createView(), 'entity' => $uitslag);
    }

    /**
     * @Route("/new", name="admin_uitslag_new")
     * @Template("CyclearGameBundle:Uitslag/Admin:new.html.twig")
     */
    public function newAction(Request $request)
    {
        $uitslag = new Uitslag();
        $em = $this->getDoctrine()->getManager();
        $seizoen = $em->getRepository("CyclearGameBundle:Seizoen")->getCurrent();
        $em = $this->getDoctrine()->getManager();
        $form = $this->createForm(new UitslagType(), $uitslag, array('seizoen' => $seizoen));
        if ('POST' === $request->getMethod()) {
            $form->submit($request);
            if ($form->isValid()) {
                $em->persist($uitslag);
                $em->flush();
                return $this->redirect($this->generateUrl('admin_uitslag'));
            }
        }
        return array('form' => $form->createView());
    }

    /**
     * Displays a form to create a new Periode entity.
     *
     * @Route("/create", name="admin_uitslag_create")
     * @Template("CyclearGameBundle:Uitslag/Admin:create.html.twig")
     */
    public function createAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $uitslagManager = $this->get('cyclear_game.manager.uitslag');
        $wedstrijdManager = $this->get('cyclear_game.manager.wedstrijd');
        $crawlerManager = $this->get('eriktrapman_cqparser.crawler_manager');
        $rennerManager = $this->get('cyclear_game.manager.renner');
        $options = array();
        $options['crawler_manager'] = $crawlerManager;
        $options['wedstrijd_manager'] = $wedstrijdManager;
        $options['uitslag_manager'] = $uitslagManager;
        $options['request'] = $request;
        $options['seizoen'] = $em->getRepository("CyclearGameBundle:Seizoen")->getCurrent();
        $options['renner_manager'] = $rennerManager;
        $options['default_date'] = new DateTime();
        $form = $this->createForm(new \Cyclear\GameBundle\Form\UitslagCreateType(), null, $options);
        if ($request->isXmlHttpRequest()) {
            $form->submit($request);

            $twig = $this->get('twig');
            $templateFile = "CyclearGameBundle:Uitslag/Admin:_ajaxTemplate.html.twig";
            $templateContent = $twig->loadTemplate($templateFile);

            // Render the whole template including any layouts etc
            $body = $templateContent->render(array("form" => $form->createView()));
            return new Response($body);
        }
        if ($request->getMethod() == 'POST') {
            $form->submit($request);
            /** @var Wedstrijd $wedstrijd */
            $wedstrijd = $form->get('wedstrijd')->getData();
            $uitslagType = $form->get('wedstrijd')->get('uitslagtype')->getData();
            $wedstrijd->setGeneralClassification($uitslagType->getIsGeneralClassification());
            $wedstrijd->setFullyProcessed(true);
            $url = $form->get('url')->getData() ? $form->get('url')->getData() : $form->get('url_manual')->getData();
            // we use the last part of the URL as identifier
            $parts = explode('/', $url);
            $wedstrijd->setExternalIdentifier(end($parts));
            $uitslagen = $form->get('uitslag')->getData();
            $em->persist($wedstrijd);
            foreach ($uitslagen as $uitslag) {
                $em->persist($uitslag);
            }
            $em->flush();
            return $this->redirect($this->generateUrl('admin_uitslag_create'));
        }
        return array('form' => $form->createView());
    }
}