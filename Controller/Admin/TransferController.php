<?php

namespace Cyclear\GameBundle\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Cyclear\GameBundle\Entity\Transfer;
use Cyclear\GameBundle\Form\TransferType;

/**
 * Transfer controller.
 *
 * @Route("/admin/transfer")
 */
class TransferController extends Controller {

    /**
     * Lists all Transfer entities.
     *
     * @Route("/", name="admin_transfer")
     * @Template("CyclearGameBundle:Transfer/Admin:index.html.twig")
     */
    public function indexAction() {
        $em = $this->getDoctrine()->getEntityManager();

        $query = $em->createQuery('SELECT t FROM Cyclear\GameBundle\Entity\Transfer t ORDER BY t.id DESC');
        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
                $query, $this->get('request')->query->get('page', 1)/* page number */, 10/* limit per page */
        );
        //$entities = $query->getResult();

        return array('entities' => $pagination);
    }

    /**
     * Finds and displays a Transfer entity.
     *
     * @Route("/{id}/show", name="admin_transfer_show")
     * @Template("CyclearGameBundle:Transfer/Admin:show.html.twig")
     */
    public function showAction($id) {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('CyclearGameBundle:Transfer')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Transfer entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity' => $entity,
            'delete_form' => $deleteForm->createView(),);
    }

    /**
     * Displays a form to create a new Transfer entity.
     *
     * @Route("/new", name="admin_transfer_new")
     * @Template("CyclearGameBundle:Transfer/Admin:new.html.twig")
     */
    public function newAction() {
        $entity = new Transfer();
        $formtype = new TransferType();
        $form = $this->createForm($formtype, $entity );
        $request = $this->getRequest();
        $em = $this->getDoctrine()->getEntityManager();
        if($request->getMethod() == 'POST'){
            $form->bindRequest( $request );
            if($form->isValid()){
                $renner = $form->get('renner')->getData();
                $ploegNaar = $form->get('ploegNaar')->getData();
                $datum = $form->get('datum')->getData();
                $type = $form->get('transferType')->getData();
                $datum->add( new \DateInterval("PT".date("H")."H".date("i")."M".date('s')."S") );
                $transferManager = $this->get('cyclear_game.manager.transfer');
                $transfer = $transferManager->doAdminTransfer($renner, $ploegNaar, $datum, $type );
                $em->persist($transfer);
                $em->flush();
                return $this->redirect( $this->generateUrl('admin_transfer') );
            }
        }
        
        return array(
            'entity' => $entity,
            'form' => $form->createView()
        );
    }

    /**
     * Displays a form to edit an existing Transfer entity.
     *
     * @Route("/{id}/edit", name="admin_transfer_edit")
     * @Template("CyclearGameBundle:Transfer/Admin:edit.html.twig")
     */
    public function editAction($id) {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('CyclearGameBundle:Transfer')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Transfer entity.');
        }

        $editForm = $this->createForm(new TransferType(), $entity);
        //$deleteForm = $this->createDeleteForm($id);
        // 'delete_form' => $deleteForm->createView(),
        return array(
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
        );
    }

    /**
     * Edits an existing Transfer entity.
     *
     * @Route("/{id}/update", name="admin_transfer_update")
     * @Method("post")
     */
    public function updateAction($id) {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('CyclearGameBundle:Transfer')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Transfer entity.');
        }

        $editForm = $this->createForm(new TransferType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        $request = $this->getRequest();

        $editForm->bindRequest($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('admin_transfer_edit', array('id' => $id)));
        }

        return array(
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Deletes a Transfer entity.
     *
     * @Route("/{id}/delete", name="admin_transfer_delete")
     * @Method("post")
     */
    public function deleteAction($id) {
        $form = $this->createDeleteForm($id);
        $request = $this->getRequest();

        $form->bindRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $entity = $em->getRepository('CyclearGameBundle:Transfer')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Transfer entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('admin_transfer'));
    }

    private function createDeleteForm($id) {
        return $this->createFormBuilder(array('id' => $id))
                        ->add('id', 'hidden')
                        ->getForm()
        ;
    }

}
