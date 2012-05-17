<?php

namespace Cyclear\GameBundle\Controller\Admin;

use Symfony\Component\Validator\Exception\ValidatorException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Cyclear\GameBundle\Entity\Renner,
    Cyclear\GameBundle\Form\RennerType,
    Cyclear\GameBundle\Entity\Transfer;

/**
 * Renner controller.
 *
 * @Route("/admin/renner")
 */
class RennerController extends Controller {

    /**
     * Lists all Renner entities.
     *
     * @Route("/", name="admin_renner")
     * @Template("CyclearGameBundle:Renner/Admin:index.html.twig")
     */
    public function indexAction() {
        $em = $this->getDoctrine()->getEntityManager();
        
        //$entities = $em->getRepository('CyclearGameBundle:Renner')->findAll();
        
        $query = $em->createQuery('SELECT r FROM CyclearGameBundle:Renner r ORDER BY r.id DESC');
        
        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
                $query, $this->get('request')->query->get('page', 1)/* page number */, 10/* limit per page */
        );
        return array('pagination'=>$pagination);
    }

    /**
     * Finds and displays a Renner entity.
     *
     * @Route("/{id}/show", name="admin_renner_show")
     * @Template("CyclearGameBundle:Renner/Admin:show.html.twig")
     */
    public function showAction($id) {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('CyclearGameBundle:Renner')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Renner entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        
        //$transferRepo = $this->get('cyclear_game.transfer_repository');
        $lastTransfer = $em->getRepository('CyclearGameBundle:Renner')->getLastTransferBeforeDate(  $entity->getId(), new \DateTime("now") );
        
        return array('entity' => $entity, 'delete_form' => $deleteForm->createView(), 'lastTransfers' => $lastTransfer);
    }

    /**
     *
     * @Route("/{id}/edit", name="admin_renner_edit")
     * @Template("CyclearGameBundle:Renner/Admin:edit.html.twig")
     */
    public function editAction($id) {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('CyclearGameBundle:Renner')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Renner entity.');
        }

        $editForm = $this->createForm(new RennerType(), $entity);
        $request = $this->getRequest();

        if ($request->getMethod() == 'POST') {
            $editForm->bindRequest($request);
            $em->persist($entity);
            $em->flush();
        }

        $deleteForm = $this->createDeleteForm($id);

        return array('entity' => $entity, 'edit_form' => $editForm->createView(), 'delete_form' => $deleteForm->createView());
    }

    public function createDeleteForm($id) {
        return $this->createFormBuilder(array('id' => $id))->add('id', 'hidden')->getForm();
    }

    /**
     *
     * @Route("/new", name="admin_renner_new")
     * @Template("CyclearGameBundle:Renner/Admin:new.html.twig")
     */
    public function newAction() {
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
    public function createAction() {
        $entity = new Renner ();
        $request = $this->getRequest();
        $form = $this->createForm(new RennerType(), $entity);
        $form->bindRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $em->persist($entity);
            $em->flush();

            $transfer = new Transfer();

            return $this->redirect($this->generateUrl('admin_renner_show', array('id' => $entity->getId())));
        }

        return array('entity' => $entity, 'form' => $form->createView());
    }

    /**
     * @Route("/{id}/delete", name="admin_renner_delete")
     * @Method("post")
     */
    public function deleteAction($id) {
        $request = $this->getRequest();
        $form = $this->createDeleteForm($id);
        $form->bindRequest($request);
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $renner = $em->getRepository("CyclearGameBundle:Renner")->find($id);
            $em->remove($renner);
            $em->flush();

            return $this->redirect($this->generateUrl('admin_renner'));
        }
        throw new ValidatorException("Invalid delete form");
    }

}