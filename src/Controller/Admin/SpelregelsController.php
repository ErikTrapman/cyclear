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

use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use App\Entity\Spelregels;
use App\Form\SpelregelsType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\HttpFoundation\Request;

/**
 * Spelregels controller.
 *
 * @Route("/admin/spelregels")
 */
class SpelregelsController extends AbstractController
{
    public static function getSubscribedServices()
    {
        return array_merge(['knp_paginator' => PaginatorInterface::class],
            parent::getSubscribedServices());
    }
    /**
     * Lists all Spelregels entities.
     *
     * @Route("/", name="admin_spelregels")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository(Spelregels::class)->findAll();

        return array('entities' => $entities);
    }

    /**
     * Displays a form to create a new Spelregels entity.
     *
     * @Route("/new", name="admin_spelregels_new")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Spelregels();
        $form = $this->createForm(SpelregelsType::class, $entity);

        return array(
            'entity' => $entity,
            'form' => $form->createView()
        );
    }

    /**
     * Creates a new Spelregels entity.
     *
     * @Route("/create", name="admin_spelregels_create")
     * @Method("post")
     */
    public function createAction(Request $request)
    {
        $entity = new Spelregels();
        $form = $this->createForm(SpelregelsType::class, $entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('admin_spelregels'));

        }

        return array(
            'entity' => $entity,
            'form' => $form->createView()
        );
    }

    /**
     * Displays a form to edit an existing Spelregels entity.
     *
     * @Route("/{id}/edit", name="admin_spelregels_edit")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository(Spelregels::class)->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Spelregels entity.');
        }

        $editForm = $this->createForm(SpelregelsType::class, $entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Edits an existing Spelregels entity.
     *
     * @Route("/{id}/update", name="admin_spelregels_update")
     * @Method("post")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository(Spelregels::class)->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Spelregels entity.');
        }

        $editForm = $this->createForm(SpelregelsType::class, $entity);
        $deleteForm = $this->createDeleteForm($id);

        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('admin_spelregels_edit', array('id' => $id)));
        }

        return array(
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Deletes a Spelregels entity.
     *
     * @Route("/{id}/delete", name="admin_spelregels_delete")
     * @Method("post")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository(Spelregels::class)->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Spelregels entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('admin_spelregels'));
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', HiddenType::class)
            ->getForm();
    }
}
