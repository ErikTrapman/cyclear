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

use Monolog\Logger;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Cyclear\GameBundle\Entity\Nieuws;
use Cyclear\GameBundle\Form\NieuwsType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\HttpFoundation\Request;

/**
 * Nieuws controller.
 *
 * @Route("/admin/nieuws")
 */
class NieuwsController extends Controller
{
    /**
     * Lists all Nieuws entities.
     *
     * @Route("/", name="admin_nieuws")
     * @Template("CyclearGameBundle:Nieuws/Admin:index.html.twig")
     */
    public function indexAction(Request $request)
    {
        $em = $this->get('doctrine.orm.default_entity_manager');

        $entities = $em->getRepository('CyclearGameBundle:Nieuws')->createQueryBuilder('n')->orderBy('n.id', 'DESC');

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $entities, $request->query->get('page', 1), 20
        );

        return array('entities' => $pagination);
    }

    /**
     * Displays a form to create a new Nieuws entity.
     *
     * @Route("/new", name="admin_nieuws_new")
     * @Template("CyclearGameBundle:Nieuws/Admin:new.html.twig")
     */
    public function newAction()
    {
        $entity = new Nieuws();
        $form = $this->createForm(NieuwsType::class, $entity);

        return array(
            'entity' => $entity,
            'form' => $form->createView()
        );
    }

    /**
     * Creates a new Nieuws entity.
     *
     * @Route("/create", name="admin_nieuws_create")
     * @Method("post")
     */
    public function createAction(Request $request)
    {
        $entity = new Nieuws();
        $form = $this->createForm(NieuwsType::class, $entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('admin_nieuws'));

        }

        return array(
            'entity' => $entity,
            'form' => $form->createView()
        );
    }

    /**
     * Displays a form to edit an existing Nieuws entity.
     *
     * @Route("/{id}/edit", name="admin_nieuws_edit")
     * @Template("CyclearGameBundle:Nieuws/Admin:edit.html.twig")
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('CyclearGameBundle:Nieuws')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Nieuws entity.');
        }

        $editForm = $this->createForm(NieuwsType::class, $entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Edits an existing Nieuws entity.
     *
     * @Route("/{id}/update", name="admin_nieuws_update")
     * @Method("post")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('CyclearGameBundle:Nieuws')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Nieuws entity.');
        }

        $editForm = $this->createForm(NieuwsType::class, $entity);
        $deleteForm = $this->createDeleteForm($id);

        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('admin_nieuws_edit', array('id' => $id)));
        }

        return array(
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Deletes a Nieuws entity.
     *
     * @Route("/{id}/delete", name="admin_nieuws_delete")
     * @Method("post")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('CyclearGameBundle:Nieuws')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Nieuws entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('admin_nieuws'));
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', HiddenType::class)
            ->getForm();
    }
}
