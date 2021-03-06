<?php

namespace App\Controller\Admin;

use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use App\Entity\AwardedBadge;
use App\Form\AwardedBadgeType;

/**
 * AwardedBadge controller.
 *
 * @Route("/admin/awardedbadge")
 */
class AwardedBadgeController extends AbstractController
{
    public static function getSubscribedServices()
    {
        return array_merge(['knp_paginator' => PaginatorInterface::class],
            parent::getSubscribedServices());
    }
    /**
     * Lists all AwardedBadge entities.
     *
     * @Route("/", name="admin_awardedbadge")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository(AwardedBadge::class)->findAll();

        return array(
            'entities' => $entities,
        );
    }

    /**
     * Creates a new AwardedBadge entity.
     *
     * @Route("/", name="admin_awardedbadge_create")
     * @Method("POST")
     * @Template()
     */
    public function createAction(Request $request)
    {
        $entity = new AwardedBadge();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('admin_awardedbadge_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
        );
    }

    /**
     * Creates a form to create a AwardedBadge entity.
     *
     * @param AwardedBadge $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(AwardedBadge $entity)
    {
        $form = $this->createForm(AwardedBadgeType::class, $entity, array(
            'action' => $this->generateUrl('admin_awardedbadge_create'),
            'method' => 'POST',
        ));

        $form->add('submit', SubmitType::class, array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new AwardedBadge entity.
     *
     * @Route("/new", name="admin_awardedbadge_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new AwardedBadge();
        $form = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
        );
    }

    /**
     * Finds and displays a AwardedBadge entity.
     *
     * @Route("/{id}", name="admin_awardedbadge_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository(AwardedBadge::class)->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find AwardedBadge entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity' => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing AwardedBadge entity.
     *
     * @Route("/{id}/edit", name="admin_awardedbadge_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository(AwardedBadge::class)->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find AwardedBadge entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Creates a form to edit a AwardedBadge entity.
     *
     * @param AwardedBadge $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm(AwardedBadge $entity)
    {
        $form = $this->createForm(AwardedBadgeType::class, $entity, array(
            'action' => $this->generateUrl('admin_awardedbadge_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', SubmitType::class, array('label' => 'Update'));

        return $form;
    }

    /**
     * Edits an existing AwardedBadge entity.
     *
     * @Route("/{id}", name="admin_awardedbadge_update")
     * @Method("PUT")
     * @Template()
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository(AwardedBadge::class)->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find AwardedBadge entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('admin_awardedbadge_edit', array('id' => $id)));
        }

        return array(
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Deletes a AwardedBadge entity.
     *
     * @Route("/{id}", name="admin_awardedbadge_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository(AwardedBadge::class)->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find AwardedBadge entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('admin_awardedbadge'));
    }

    /**
     * Creates a form to delete a AwardedBadge entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('admin_awardedbadge_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', SubmitType::class, array('label' => 'Delete'))
            ->getForm();
    }
}
