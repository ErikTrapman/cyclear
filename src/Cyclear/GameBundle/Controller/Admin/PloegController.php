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

use Cyclear\GameBundle\Entity\Ploeg;
use Cyclear\GameBundle\Form\PloegType;
use Doctrine\DBAL\Types\Type;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Ploeg controller.
 *
 * @Route("/admin/ploeg")
 */
class PloegController extends Controller {

    /**
     * Lists all Ploeg entities.
     *
     * @Route("/", name="admin_ploeg")
     * @Template("CyclearGameBundle:Ploeg/Admin:index.html.twig")
     */
    public function indexAction(Request $request) {
        
        $filter = $this->createForm('ploeg_filter');

        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery("SELECT p FROM Cyclear\GameBundle\Entity\Ploeg p ORDER BY p.id DESC");

        $config = $em->getConfiguration();
        $config->addFilter("naam", "Cyclear\GameBundle\Filter\Ploeg\PloegNaamFilter");

        if ($request->getMethod() == 'POST') {
            $filter->submit($request);
            //$data = $filter->get('user')->getData();
            if ($filter->isValid()) {
                if ($filter->get('naam')->getData()) {
                    $em->getFilters()->enable("naam")->setParameter("naam", $filter->get('naam')->getData(), Type::getType(Type::STRING)->getBindingType());
                }
                if ($filter->get('user')->getData()) {
                    $em->getFilters()->enable("user")->setParameter("user", $filter->get('user')->getData(), Type::getType(Type::STRING)->getBindingType());
                }
                if ($filter->get('seizoen')->getData()) {
                    $em->getFilters()->enable("seizoen")->setParameter("seizoen", $filter->get('seizoen')->getData(), Type::getType(Type::STRING)->getBindingType());
                }
            }
        }
        $paginator = $this->get('knp_paginator');
        $entities = $paginator->paginate(
            $query, $request->query->get('page', 1)/* page number */, 20/* limit per page */
        );
        
        //$entities = $em->getRepository('CyclearGameBundle:Ploeg')->findAll();
        //$entities = $query->getResult();

        return array('entities' => $entities, 'filter' => $filter->createView());
    }


    /**
     * Displays a form to create a new Ploeg entity.
     *
     * @Route("/new", name="admin_ploeg_new")
     * @Template("CyclearGameBundle:Ploeg/Admin:new.html.twig")
     */
    public function newAction() {
        $entity = new Ploeg();
        $form = $this->createForm(new PloegType(), $entity);

        return array(
            'entity' => $entity,
            'form' => $form->createView()
        );
    }

    /**
     * Creates a new Ploeg entity.
     *
     * @Route("/create", name="admin_ploeg_create")
     * @Method("post")
     */
    public function createAction(Request $request) {
        $entity = new Ploeg();
        $form = $this->createForm(new PloegType(), $entity);
        $form->submit($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('admin_ploeg'));
        }

        return array(
            'entity' => $entity,
            'form' => $form->createView()
        );
    }

    /**
     * Displays a form to edit an existing Ploeg entity.
     *
     * @Route("/{id}/edit", name="admin_ploeg_edit")
     * @Template("CyclearGameBundle:Ploeg/Admin:edit.html.twig")
     */
    public function editAction($id) {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('CyclearGameBundle:Ploeg')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Ploeg entity.');
        }

        $editForm = $this->createForm(new PloegType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Edits an existing Ploeg entity.
     *
     * @Route("/{id}/update", name="admin_ploeg_update")
     * @Method("post")
     */
    public function updateAction(Request $request, $id) {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('CyclearGameBundle:Ploeg')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Ploeg entity.');
        }

        $editForm = $this->createForm(new PloegType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        $editForm->submit($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('admin_ploeg'));
        }

        return array(
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Deletes a Ploeg entity.
     *
     * @Route("/{id}/delete", name="admin_ploeg_delete")
     * @Method("post")
     */
    public function deleteAction(Request $request, $id) {
        $form = $this->createDeleteForm($id);

        $form->submit($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('CyclearGameBundle:Ploeg')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Ploeg entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('admin_ploeg'));
    }

    private function createDeleteForm($id) {
        return $this->createFormBuilder(array('id' => $id))
                        ->add('id', 'hidden')
                        ->getForm()
        ;
    }

}
