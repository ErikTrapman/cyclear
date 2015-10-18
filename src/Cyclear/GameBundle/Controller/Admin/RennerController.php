<?php

/*
 * This file is part of the Cyclear-game package.
 *
 * (c) Erik Trapman <veggatron@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cyclear\GameBundle\Controller\Admin;

use Symfony\Component\Validator\Exception\ValidatorException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Doctrine\DBAL\Types\Type;
use Cyclear\GameBundle\Entity\Renner,
    Cyclear\GameBundle\Form\RennerType,
    Cyclear\GameBundle\Entity\Transfer;

/**
 * Renner controller.
 *
 * @Route("/admin/renner")
 */
class RennerController extends Controller
{

    /**
     * Lists all Renner entities.
     *
     * @Route("/", name="admin_renner")
     * @Template("CyclearGameBundle:Renner/Admin:index.html.twig")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $query = $em->createQuery('SELECT r FROM CyclearGameBundle:Renner r ORDER BY r.id DESC');
        $filter = $this->createForm('renner_filter');
        $config = $em->getConfiguration();
        $config->addFilter("naam", "Cyclear\GameBundle\Filter\RennerNaamFilter");
        if ($this->getRequest()->getMethod() == 'POST') {
            $filter->submit($this->getRequest());
            if ($filter->isValid()) {
                if ($filter->get('naam')->getData()) {
                    $em->getFilters()->enable("naam")->setParameter("naam", $filter->get('naam')->getData(), Type::getType(Type::STRING)->getBindingType());
                }
            }
        }


        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query, $this->get('request')->query->get('page', 1)/* page number */, 20/* limit per page */
        );
        return array('pagination' => $pagination, 'filter' => $filter->createView());
    }

    /**
     *
     * @Route("/{id}/edit", name="admin_renner_edit")
     * @Template("CyclearGameBundle:Renner/Admin:edit.html.twig")
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('CyclearGameBundle:Renner')->findOneBy(array('cqranking_id' => $id));

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Renner entity.');
        }

        $editForm = $this->createForm(new RennerType(), $entity);
        $request = $this->getRequest();

        if ($request->getMethod() == 'POST') {
            $editForm->submit($request);
            $em->persist($entity);
            $em->flush();
        }

        $deleteForm = $this->createDeleteForm($id);

        return array('entity' => $entity, 'edit_form' => $editForm->createView(), 'delete_form' => $deleteForm->createView());
    }

    public function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))->add('id', 'hidden')->getForm();
    }

    /**
     *
     * @Route("/new", name="admin_renner_new")
     * @Template("CyclearGameBundle:Renner/Admin:new.html.twig")
     */
    public function newAction()
    {
        $entity = new Renner ();
        $form = $this->createForm(new RennerType(), $entity);

        return array('entity' => $entity, 'form' => $form->createView());
    }

    /**
     *
     * @Route("/create", name="admin_renner_create")
     * @Method("post")
     * @Template("CyclearGameBundle:Renner/Admin:new.html.twig")
     */
    public function createAction()
    {
        $entity = new Renner ();
        $request = $this->getRequest();
        $form = $this->createForm(new RennerType(), $entity);
        $form->submit($request);

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
    public function deleteAction($id)
    {
        $request = $this->getRequest();
        $form = $this->createDeleteForm($id);
        $form->submit($request);
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $renner = $em->getRepository("CyclearGameBundle:Renner")->find($id);
            $em->remove($renner);
            $em->flush();

            return $this->redirect($this->generateUrl('admin_renner'));
        }
        throw new ValidatorException("Invalid delete form");
    }
}