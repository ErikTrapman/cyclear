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
use Monolog\Logger;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use App\Entity\UitslagType;
use App\Form\UitslagTypeType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\HttpFoundation\Request;

/**
 * UitslagType controller.
 *
 * @Route("/admin/uitslagtype")
 */
class UitslagTypeController extends AbstractController
{
    public static function getSubscribedServices()
    {
        return array_merge(['knp_paginator' => PaginatorInterface::class],
            parent::getSubscribedServices());
    }
    /**
     * Lists all UitslagType entities.
     *
     * @Route("/", name="admin_uitslagtype")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository(UitslagType::class)->findAll();

        return array('entities' => $entities);
    }


    /**
     * Displays a form to create a new UitslagType entity.
     *
     * @Route("/new", name="admin_uitslagtype_new")
     * @Template()
     */
    public function newAction()
    {
        $entity = new UitslagType();
        $form = $this->createForm(UitslagTypeType::class, $entity);

        return array(
            'entity' => $entity,
            'form' => $form->createView()
        );
    }

    /**
     * Creates a new UitslagType entity.
     *
     * @Route("/create", name="admin_uitslagtype_create")
     * @Method("post")
     */
    public function createAction(Request $request)
    {
        $entity = new UitslagType();
        $form = $this->createForm(UitslagTypeType::class, $entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('admin_uitslagtype'));

        }

        return array(
            'entity' => $entity,
            'form' => $form->createView()
        );
    }

    /**
     * Displays a form to edit an existing UitslagType entity.
     *
     * @Route("/{id}/edit", name="admin_uitslagtype_edit")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository(UitslagType::class)->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find UitslagType entity.');
        }

        $editForm = $this->createForm(UitslagTypeType::class, $entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Edits an existing UitslagType entity.
     *
     * @Route("/{id}/update", name="admin_uitslagtype_update")
     * @Method("post")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository(UitslagType::class)->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find UitslagType entity.');
        }

        $editForm = $this->createForm(UitslagTypeType::class, $entity);
        $deleteForm = $this->createDeleteForm($id);

        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('admin_uitslagtype_edit', array('id' => $id)));
        }

        return array(
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Deletes a UitslagType entity.
     *
     * @Route("/{id}/delete", name="admin_uitslagtype_delete")
     * @Method("post")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository(UitslagType::class)->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find UitslagType entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('admin_uitslagtype'));
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', HiddenType::class)
            ->getForm();
    }
}
