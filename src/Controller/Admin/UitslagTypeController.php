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
     * @Route ("/", name="admin_uitslagtype")
     *
     * @Template ()
     *
     * @return UitslagType[][]
     *
     * @psalm-return array{entities: array<UitslagType>}
     */
    public function indexAction(): array
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository(UitslagType::class)->findAll();

        return ['entities' => $entities];
    }

    /**
     * Displays a form to create a new UitslagType entity.
     *
     * @Route ("/new", name="admin_uitslagtype_new")
     *
     * @Template ()
     *
     * @return (UitslagType|\Symfony\Component\Form\FormView)[]
     *
     * @psalm-return array{entity: UitslagType, form: \Symfony\Component\Form\FormView}
     */
    public function newAction(): array
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
     * @Route ("/create", name="admin_uitslagtype_create", methods={"POST"})
     *
     * @return (UitslagType|\Symfony\Component\Form\FormView)[]|\Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @psalm-return \Symfony\Component\HttpFoundation\RedirectResponse|array{entity: UitslagType, form: \Symfony\Component\Form\FormView}
     */
    public function createAction(Request $request): array|\Symfony\Component\HttpFoundation\RedirectResponse
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
     * @Route ("/{id}/edit", name="admin_uitslagtype_edit")
     *
     * @Template ()
     *
     * @psalm-return array{entity: UitslagType, edit_form: \Symfony\Component\Form\FormView, delete_form: mixed}
     * @param mixed $id
     * @return (UitslagType|\Symfony\Component\Form\FormView|mixed)[]
     */
    public function editAction($id): array
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
     * @Route ("/{id}/update", name="admin_uitslagtype_update", methods={"POST"})
     *
     * @psalm-return \Symfony\Component\HttpFoundation\RedirectResponse|array{entity: UitslagType, edit_form: \Symfony\Component\Form\FormView, delete_form: mixed}
     * @param mixed $id
     * @return (UitslagType|\Symfony\Component\Form\FormView|mixed)[]|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function updateAction(Request $request, $id): array|\Symfony\Component\HttpFoundation\RedirectResponse
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
     * @Route ("/{id}/delete", name="admin_uitslagtype_delete", methods={"POST"})
     * @param mixed $id
     */
    public function deleteAction(Request $request, $id): \Symfony\Component\HttpFoundation\RedirectResponse
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

    private function createDeleteForm($id): \Symfony\Component\Form\FormInterface
    {
        return $this->createFormBuilder(['id' => $id])
            ->add('id', HiddenType::class)
            ->getForm();
    }
}
