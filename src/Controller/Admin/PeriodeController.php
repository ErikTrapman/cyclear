<?php declare(strict_types=1);

/*
 * This file is part of the Cyclear-game package.
 *
 * (c) Erik Trapman <veggatron@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\Admin;

use App\Entity\Periode;
use App\Form\PeriodeType;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\HttpFoundation\Request;

/**
 * Periode controller.
 *
 * @Route("/admin/periode")
 */
class PeriodeController extends AbstractController
{
    public static function getSubscribedServices()
    {
        return array_merge(['knp_paginator' => PaginatorInterface::class],
            parent::getSubscribedServices());
    }

    /**
     * Lists all Periode entities.
     *
     * @Route("/", name="admin_periode")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository(Periode::class)->createQueryBuilder('p')->orderBy('p.eind', 'DESC');

        $paginator = $this->get('knp_paginator');
        $entities = $paginator->paginate(
            $entities, $request->query->get('page', 1)/* page number */, 20/* limit per page */
        );

        return ['entities' => $entities];
    }

    /**
     * Displays a form to create a new Periode entity.
     *
     * @Route("/new", name="admin_periode_new")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Periode();
        $form = $this->createForm(PeriodeType::class, $entity);

        return [
            'entity' => $entity,
            'form' => $form->createView(),
        ];
    }

    /**
     * Creates a new Periode entity.
     *
     * @Route("/create", name="admin_periode_create")
     * @Method("post")
     */
    public function createAction(Request $request)
    {
        $entity = new Periode();
        $form = $this->createForm(PeriodeType::class, $entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('admin_periode'));
        }

        return [
            'entity' => $entity,
            'form' => $form->createView(),
        ];
    }

    /**
     * Displays a form to edit an existing Periode entity.
     *
     * @Route("/{id}/edit", name="admin_periode_edit")
     * @Template()
     * @param mixed $id
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository(Periode::class)->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Periode entity.');
        }

        $editForm = $this->createForm(PeriodeType::class, $entity);
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
     * @Route("/{id}/update", name="admin_periode_update")
     * @Method("post")
     * @param mixed $id
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository(Periode::class)->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Periode entity.');
        }

        $editForm = $this->createForm(PeriodeType::class, $entity);
        $deleteForm = $this->createDeleteForm($id);

        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('admin_periode_edit', ['id' => $id]));
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
     * @Route("/{id}/delete", name="admin_periode_delete")
     * @Method("post")
     * @param mixed $id
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository(Periode::class)->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Periode entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('admin_periode'));
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(['id' => $id])
            ->add('id', HiddenType::class)
            ->getForm();
    }
}
