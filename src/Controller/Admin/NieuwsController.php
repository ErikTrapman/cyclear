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

use App\Entity\Nieuws;
use App\Form\NieuwsType;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\HttpFoundation\Request;

/**
 * Nieuws controller.
 *
 * @Route("/admin/nieuws")
 */
class NieuwsController extends AbstractController
{
    public static function getSubscribedServices()
    {
        return array_merge(['knp_paginator' => PaginatorInterface::class],
            parent::getSubscribedServices());
    }

    /**
     * Lists all Nieuws entities.
     *
     * @Route("/", name="admin_nieuws")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $em = $this->get('doctrine');

        $entities = $em->getRepository(Nieuws::class)->createQueryBuilder('n')->orderBy('n.id', 'DESC');

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $entities, $request->query->get('page', 1), 20
        );

        return ['entities' => $pagination];
    }

    /**
     * Displays a form to create a new Nieuws entity.
     *
     * @Route("/new", name="admin_nieuws_new")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Nieuws();
        $form = $this->createForm(NieuwsType::class, $entity);

        return [
            'entity' => $entity,
            'form' => $form->createView(),
        ];
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

        return [
            'entity' => $entity,
            'form' => $form->createView(),
        ];
    }

    /**
     * Displays a form to edit an existing Nieuws entity.
     *
     * @Route("/{id}/edit", name="admin_nieuws_edit")
     * @Template()
     * @param mixed $id
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository(Nieuws::class)->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Nieuws entity.');
        }

        $editForm = $this->createForm(NieuwsType::class, $entity);
        $deleteForm = $this->createDeleteForm($id);

        return [
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ];
    }

    /**
     * Edits an existing Nieuws entity.
     *
     * @Route("/{id}/update", name="admin_nieuws_update")
     * @Method("post")
     * @param mixed $id
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository(Nieuws::class)->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Nieuws entity.');
        }

        $editForm = $this->createForm(NieuwsType::class, $entity);
        $deleteForm = $this->createDeleteForm($id);

        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('admin_nieuws_edit', ['id' => $id]));
        }

        return [
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ];
    }

    /**
     * Deletes a Nieuws entity.
     *
     * @Route("/{id}/delete", name="admin_nieuws_delete")
     * @Method("post")
     * @param mixed $id
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository(Nieuws::class)->find($id);

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
        return $this->createFormBuilder(['id' => $id])
            ->add('id', HiddenType::class)
            ->getForm();
    }
}
