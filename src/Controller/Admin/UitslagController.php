<?php

/*
 * This file is part of the Cyclear-game package.
 *
 * (c) Erik Trapman <veggatron@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\Admin;

use App\Entity\Seizoen;
use App\Entity\Uitslag;
use App\Entity\Wedstrijd;
use App\EntityManager\RennerManager;
use App\EntityManager\UitslagManager;
use App\Form\UitslagConfirmType;
use App\Form\UitslagCreateType;
use App\Form\UitslagNewType;
use App\Form\UitslagType;
use App\Form\WedstrijdType;
use DateTime;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use stdClass;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 *
 * @Route("admin/uitslag")
 *
 */
class UitslagController extends AbstractController
{

    /**
     * @Route("/", name="admin_uitslag")
     * @Template()
     */
    public function indexAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();

        $query = $em->createQuery('SELECT w FROM App\Entity\Uitslag w ORDER BY w.id DESC');

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query, $request->query->get('page', 1)/* page number */, 20/* limit per page */
        );
        $seizoen = $em->getRepository(Seizoen::class)->getCurrent();
        return array('pagination' => $pagination, 'seizoen' => $seizoen);
    }

    /**
     * @Route("/{uitslag}/edit", name="admin_uitslag_edit")
     * @Template()
     */
    public function editAction(Request $request, Uitslag $uitslag)
    {
        $em = $this->getDoctrine()->getManager();
        $seizoen = $uitslag->getWedstrijd()->getSeizoen();
        $form = $this->createForm(UitslagType::class, $uitslag, array('seizoen' => $seizoen));
        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em->flush();
                return $this->redirect($this->generateUrl('admin_uitslag_edit', array('uitslag' => $uitslag->getId())));
            }
        }

        return array('form' => $form->createView(), 'entity' => $uitslag);
    }

    /**
     * @Route("/new", name="admin_uitslag_new")
     * @Template()
     */
    public function newAction(Request $request)
    {
        $uitslag = new Uitslag();
        $em = $this->getDoctrine()->getManager();
        $seizoen = $em->getRepository(Seizoen::class)->getCurrent();
        $em = $this->getDoctrine()->getManager();
        $form = $this->createForm(UitslagType::class, $uitslag, array('seizoen' => $seizoen));
        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);
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
     * @Template()
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
        $options['seizoen'] = $em->getRepository(Seizoen::class)->getCurrent();
        $options['renner_manager'] = $rennerManager;
        $options['default_date'] = new DateTime();
        $form = $this->createForm(UitslagCreateType::class, null, $options);
        if ($request->isXmlHttpRequest()) {
            $form->handleRequest($request);

            $twig = $this->get('twig');
            $templateFile = "uitslag/Admin:_ajaxTemplate.html.twig";
            $templateContent = $twig->loadTemplate($templateFile);

            // Render the whole template including any layouts etc
            $body = $templateContent->render(array("form" => $form->createView()));
            return new Response($body);
        }
        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
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