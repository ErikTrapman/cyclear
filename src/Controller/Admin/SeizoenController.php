<?php declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Seizoen;
use App\Form\SeizoenType;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Seizoen controller.
 *
 * @Route("/admin/seizoen")
 */
class SeizoenController extends AbstractController
{
    public static function getSubscribedServices()
    {
        return array_merge(['knp_paginator' => PaginatorInterface::class],
            parent::getSubscribedServices());
    }

    /**
     * Lists all Seizoen entities.
     *
     * @Route ("/", name="admin_seizoen")
     *
     * @Template ()
     *
     * @return Seizoen[][]
     *
     * @psalm-return array{entities: array<Seizoen>}
     */
    public function indexAction(): array
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository(Seizoen::class)->findAll();

        return ['entities' => $entities];
    }

    /**
     * Displays a form to create a new Seizoen entity.
     *
     * @Route ("/new", name="admin_seizoen_new")
     *
     * @Template ()
     *
     * @return (Seizoen|\Symfony\Component\Form\FormView)[]
     *
     * @psalm-return array{entity: Seizoen, form: \Symfony\Component\Form\FormView}
     */
    public function newAction(): array
    {
        $entity = new Seizoen();
        $form = $this->createForm(SeizoenType::class, $entity);

        return [
            'entity' => $entity,
            'form' => $form->createView(),
        ];
    }

    /**
     * Creates a new Seizoen entity.
     *
     * @Route ("/create", name="admin_seizoen_create", methods={"POST"})
     *
     * @return (Seizoen|\Symfony\Component\Form\FormView)[]|\Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @psalm-return \Symfony\Component\HttpFoundation\RedirectResponse|array{entity: Seizoen, form: \Symfony\Component\Form\FormView}
     */
    public function createAction(Request $request): array|\Symfony\Component\HttpFoundation\RedirectResponse
    {
        $entity = new Seizoen();
        $form = $this->createForm(SeizoenType::class, $entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            if ($entity->isCurrent()) {
                // find other seasons that are also current
                foreach ($em->getRepository(Seizoen::class)->findBy(['current' => true]) as $otherSeason) {
                    if ($otherSeason === $entity) {
                        continue;
                    }
                    $otherSeason->setCurrent(false);
                    $otherSeason->setClosed(true);
                }
            }
            $em->flush();
            return $this->redirect($this->generateUrl('admin_seizoen'));
        }
        return [
            'entity' => $entity,
            'form' => $form->createView(),
        ];
    }

    /**
     * Displays a form to edit an existing Seizoen entity.
     *
     * @Route ("/{id}/edit", name="admin_seizoen_edit")
     *
     * @Template ()
     *
     * @return (Seizoen|\Symfony\Component\Form\FormView|mixed)[]
     *
     * @psalm-return array{entity: Seizoen, edit_form: \Symfony\Component\Form\FormView, delete_form: mixed}
     */
    public function editAction($id): array
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository(Seizoen::class)->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find entity.');
        }

        $editForm = $this->createForm(SeizoenType::class, $entity);
        $deleteForm = $this->createDeleteForm($id);

        return [
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ];
    }

    /**
     * Edits an existing Seizoen entity.
     *
     * @Route ("/{id}/update", name="admin_seizoen_update", methods={"POST"})
     *
     * @return (Seizoen|\Symfony\Component\Form\FormView|mixed)[]|\Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @psalm-return \Symfony\Component\HttpFoundation\RedirectResponse|array{entity: Seizoen, edit_form: \Symfony\Component\Form\FormView, delete_form: mixed}
     */
    public function updateAction(Request $request, $id): array|\Symfony\Component\HttpFoundation\RedirectResponse
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository(Seizoen::class)->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find entity.');
        }

        $editForm = $this->createForm(SeizoenType::class, $entity);
        $deleteForm = $this->createDeleteForm($id);

        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('admin_seizoen_edit', ['id' => $id]));
        }

        return [
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ];
    }

    /**
     * Deletes a Seizoen entity.
     *
     * @Route ("/{id}/delete", name="admin_seizoen_delete", methods={"POST"})
     */
    public function deleteAction(Request $request, $id): \Symfony\Component\HttpFoundation\RedirectResponse
    {
        $form = $this->createDeleteForm($id);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository(Seizoen::class)->find($id);

            if ($entity->isCurrent()) {
                $this->addFlash('error', 'Je kunt niet een `huidig` seizoen verwijderen.');
                return $this->redirect($this->generateUrl('admin_seizoen'));
            }

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('admin_seizoen'));
    }

    private function createDeleteForm($id): \Symfony\Component\Form\FormInterface
    {
        return $this->createFormBuilder(['id' => $id])
            ->add('id', HiddenType::class)
            ->getForm();
    }
}
