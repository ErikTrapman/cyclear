<?php declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Wedstrijd;
use App\Form\WedstrijdType;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/wedstrijd")
 */
class WedstrijdController extends AbstractController
{
    public static function getSubscribedServices()
    {
        return array_merge(['knp_paginator' => PaginatorInterface::class],
            parent::getSubscribedServices());
    }

    /**
     * @Route ("/", name="admin_wedstrijd")
     *
     * @Template ()
     *
     * @psalm-return array{pagination: mixed}
     */
    public function indexAction(Request $request): array
    {
        $em = $this->getDoctrine()->getManager();

        $query = $em->createQuery('SELECT w FROM App\Entity\Wedstrijd w ORDER BY w.id DESC');

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query, $request->query->get('page', 1)/* page number */, 20/* limit per page */
        );
        return ['pagination' => $pagination];
    }

    /**
     * Displays a form to create a new Periode entity.
     *
     * @Route ("/new", name="admin_wedstrijd_new")
     *
     * @Template ()
     *
     * @return (Wedstrijd|\Symfony\Component\Form\FormView)[]
     *
     * @psalm-return array{entity: Wedstrijd, form: \Symfony\Component\Form\FormView}
     */
    public function newAction(): array
    {
        $entity = new Wedstrijd();
        $form = $this->createForm(WedstrijdType::class, $entity);

        return [
            'entity' => $entity,
            'form' => $form->createView(),
        ];
    }

    /**
     * Creates a new Periode entity.
     *
     * @Route ("/create", name="admin_wedstrijd_create", methods={"POST"})
     *
     * @return (Wedstrijd|\Symfony\Component\Form\FormView)[]|\Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @psalm-return \Symfony\Component\HttpFoundation\RedirectResponse|array{entity: Wedstrijd, form: \Symfony\Component\Form\FormView}
     */
    public function createAction(Request $request): array|\Symfony\Component\HttpFoundation\RedirectResponse
    {
        $entity = new Wedstrijd();
        $form = $this->createForm(WedstrijdType::class, $entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('admin_wedstrijd'));
        }

        return [
            'entity' => $entity,
            'form' => $form->createView(),
        ];
    }

    /**
     * Displays a form to edit an existing Periode entity.
     *
     * @Route ("/{id}/edit", name="admin_wedstrijd_edit")
     *
     * @Template ()
     *
     * @psalm-return array{entity: Wedstrijd, edit_form: \Symfony\Component\Form\FormView, delete_form: mixed}
     * @param mixed $id
     * @return (Wedstrijd|\Symfony\Component\Form\FormView|mixed)[]
     */
    public function editAction($id): array
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository(Wedstrijd::class)->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Wedstrijd entity.');
        }

        $editForm = $this->createForm(WedstrijdType::class, $entity);
        $deleteForm = $this->createDeleteForm($id);

        return [
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ];
    }

    /**
     * Edits an existing Periode entity.
     *
     * @Route ("/{id}/update", name="admin_wedstrijd_update", methods={"POST"})
     *
     * @psalm-return \Symfony\Component\HttpFoundation\RedirectResponse|array{entity: Wedstrijd, edit_form: \Symfony\Component\Form\FormView, delete_form: mixed}
     * @param mixed $id
     * @return (Wedstrijd|\Symfony\Component\Form\FormView|mixed)[]|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function updateAction(Request $request, $id): array|\Symfony\Component\HttpFoundation\RedirectResponse
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository(Wedstrijd::class)->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Wedstrijd entity.');
        }

        $editForm = $this->createForm(WedstrijdType::class, $entity);
        $deleteForm = $this->createDeleteForm($id);

        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('admin_wedstrijd_edit', ['id' => $id]));
        }

        return [
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ];
    }

    /**
     * Deletes a Periode entity.
     *
     * @Route ("/{id}/delete", name="admin_wedstrijd_delete", methods={"POST"})
     * @param mixed $id
     */
    public function deleteAction(Request $request, $id): \Symfony\Component\HttpFoundation\RedirectResponse
    {
        $form = $this->createDeleteForm($id);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository(Wedstrijd::class)->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Wedstrijd entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('admin_wedstrijd'));
    }

    private function createDeleteForm($id): \Symfony\Component\Form\FormInterface
    {
        return $this->createFormBuilder(['id' => $id])
            ->add('id', HiddenType::class)
            ->getForm();
    }
}
