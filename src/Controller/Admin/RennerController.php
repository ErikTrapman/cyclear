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

use App\Form\Filter\RennerFilterType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Exception\ValidatorException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Doctrine\DBAL\Types\Type;
use App\Entity\Renner,
    App\Form\RennerType,
    App\Entity\Transfer;

/**
 * Renner controller.
 *
 * @Route("/admin/renner")
 */
class RennerController extends AbstractController
{

    /**
     * Lists all Renner entities.
     *
     * @Route("/", name="admin_renner")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $query = $em->createQuery('SELECT r FROM App\Entity\Renner r ORDER BY r.id DESC');
        $filter = $this->createForm(RennerFilterType::class);
        $config = $em->getConfiguration();
        $config->addFilter("naam", "App\Filter\RennerNaamFilter");
        if ($request->getMethod() == 'POST') {
            $filter->handleRequest($request);
            if ($filter->isValid()) {
                if ($filter->get('naam')->getData()) {
                    $em->getFilters()->enable("naam")->setParameter("naam", $filter->get('naam')->getData(), Type::getType(Type::STRING)->getBindingType());
                }
            }
        }


        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query, $request->query->get('page', 1)/* page number */, 20/* limit per page */
        );
        return array('pagination' => $pagination, 'filter' => $filter->createView());
    }

    /**
     *
     * @Route("/{id}/edit", name="admin_renner_edit")
     * @Template()
     */
    public function editAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository(Renner::class)->findOneBy(array('cqranking_id' => $id));

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Renner entity.');
        }

        $editForm = $this->createForm(RennerType::class, $entity);

        if ($request->getMethod() == 'POST') {
            $editForm->handleRequest($request);
            $em->persist($entity);
            $em->flush();
        }

        $deleteForm = $this->createDeleteForm($id);

        return array('entity' => $entity, 'edit_form' => $editForm->createView(), 'delete_form' => $deleteForm->createView());
    }

    public function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))->add('id', HiddenType::class)->getForm();
    }

    /**
     *
     * @Route("/new", name="admin_renner_new")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Renner ();
        $form = $this->createForm(RennerType::class, $entity);

        return array('entity' => $entity, 'form' => $form->createView());
    }

    /**
     *
     * @Route("/create", name="admin_renner_create")
     * @Method("post")
     * @Template()
     */
    public function createAction(Request $request)
    {
        $entity = new Renner ();
        $form = $this->createForm(RennerType::class, $entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('admin_renner'));
        }

        return array('entity' => $entity, 'form' => $form->createView());
    }

    /**
     * @Route("/{id}/delete", name="admin_renner_delete")
     * @Method("post")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $renner = $em->getRepository(Renner::class)->findOneByCQId($id);
            $em->remove($renner);
            $em->flush();

            return $this->redirect($this->generateUrl('admin_renner'));
        }
        throw new ValidatorException("Invalid delete form");
    }
}