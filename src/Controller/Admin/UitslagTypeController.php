<?php declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\UitslagType;
use App\Form\UitslagTypeType;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

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

        return ['entities' => $entities];
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

        return [
            'entity' => $entity,
            'form' => $form->createView(),
        ];
    }

    /**
     * Creates a new UitslagType entity.
     *
     * @Route("/create", name="admin_uitslagtype_create", methods={"POST"})
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

        return [
            'entity' => $entity,
            'form' => $form->createView(),
        ];
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

        return [
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ];
    }

    /**
     * Edits an existing UitslagType entity.
     *
     * @Route("/{id}/update", name="admin_uitslagtype_update", methods={"POST"})
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

            return $this->redirect($this->generateUrl('admin_uitslagtype_edit', ['id' => $id]));
        }

        return [
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ];
    }

    /**
     * Deletes a UitslagType entity.
     *
     * @Route("/{id}/delete", name="admin_uitslagtype_delete", methods={"POST"})
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
        return $this->createFormBuilder(['id' => $id])
            ->add('id', HiddenType::class)
            ->getForm();
    }
}
