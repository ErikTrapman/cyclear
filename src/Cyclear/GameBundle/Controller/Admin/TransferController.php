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

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Cyclear\GameBundle\Entity\Transfer;
use Cyclear\GameBundle\Form\Admin\Transfer\TransferType;

/**
 * Transfer controller.
 *
 * @Route("/admin/transfer")
 */
class TransferController extends Controller
{

    /**
     * Lists all Transfer entities.
     *
     * @Route("/", name="admin_transfer")
     * @Template("CyclearGameBundle:Transfer/Admin:index.html.twig")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $query = $em->createQuery('SELECT t FROM Cyclear\GameBundle\Entity\Transfer t ORDER BY t.id DESC');
        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query, $this->get('request')->query->get('page', 1)/* page number */, 20/* limit per page */
        );
        //$entities = $query->getResult();

        return array('entities' => $pagination);
    }

    /**
     * Displays a form to create a new Transfer entity.
     *
     * @Route("/new", name="admin_transfer_new")
     * @Template("CyclearGameBundle:Transfer/Admin:new.html.twig")
     */
    public function newAction()
    {
        return array();
    }

    /**
     * Displays a form to create a new Transfer entity.
     *
     * @Route("/new-draft", name="admin_transfer_new_draft")
     * @Template("CyclearGameBundle:Transfer/Admin:newDraft.html.twig")
     */
    public function newDraftAction(\Symfony\Component\HttpFoundation\Request $request)
    {
        $entity = new Transfer();
        $entity->setTransferType(Transfer::DRAFTTRANSFER);
        $entity->setDatum(new \DateTime());
        $form = $this->getTransferForm($entity);
        if ('POST' === $request->getMethod()) {
            $form->submit($request);
            if ($form->isValid()) {
                $form->get('datum')->getData()->setTime(date('H'), date('i'), date('s'));
                $transferManager = $this->get('cyclear_game.manager.transfer');
                $transferManager->doDraftTransfer($entity);
                $this->getDoctrine()->getManager()->flush();
                return $this->redirect($this->generateUrl('admin_transfer'));
            }
        }
        return array('form' => $form->createView());
    }

    /**
     * Displays a form to create a new Transfer entity.
     *
     * @Route("/new-exchange", name="admin_transfer_new_exchange")
     * @Template("CyclearGameBundle:Transfer/Admin:newExchange.html.twig")
     */
    public function newExchangeAction(\Symfony\Component\HttpFoundation\Request $request)
    {
        $entity = new Transfer();
        $entity->setTransferType(Transfer::ADMINTRANSFER);
        $entity->setDatum(new \DateTime());
        //$entity->setSeizoen($seizoen);
        $form = $this->getTransferForm($entity);
        if ('POST' === $request->getMethod()) {
            $form->submit($request);
            if ($form->isValid()) {
                $form->get('datum')->getData()->setTime(date('H'), date('i'), date('s'));
                $transferManager = $this->get('cyclear_game.manager.transfer');
                $transferManager->doExchangeTransfer(
                    $form->get('renner')->getData(), $form->get('renner2')->getData(), $form->get('datum')->getData(), $form->get('seizoen')->getData());
                $this->getDoctrine()->getManager()->flush();
                return $this->redirect($this->generateUrl('admin_transfer'));
            }
        }
        return array('form' => $form->createView());
    }

    private function getTransferForm($entity)
    {
        $seizoen = $this->getDoctrine()->getRepository("CyclearGameBundle:Seizoen")->getCurrent();
        $formtype = new TransferType();
        $form = $this->createForm($formtype, $entity, array('transfertype' => $entity->getTransferType(), 'seizoen' => $seizoen));
        return $form;
    }

    /**
     * Displays a form to edit an existing Transfer entity.
     *
     * @Route("/{id}/edit", name="admin_transfer_edit")
     * @Template("CyclearGameBundle:Transfer/Admin:edit.html.twig")
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('CyclearGameBundle:Transfer')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Transfer entity.');
        }

        $editForm = $this->createForm(new \Cyclear\GameBundle\Form\Admin\Transfer\TransferEditType(), $entity);
        $deleteForm = $this->createDeleteForm($id);
        return array(
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView()
        );
    }

    /**
     * Edits an existing Transfer entity.
     *
     * @Route("/{id}/update", name="admin_transfer_update")
     * @Method("post")
     */
    public function updateAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('CyclearGameBundle:Transfer')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Transfer entity.');
        }

        $editForm = $this->createForm(new \Cyclear\GameBundle\Form\Admin\Transfer\TransferEditType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        $request = $this->getRequest();

        $editForm->submit($request);

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
    public function deleteAction($id)
    {
        $form = $this->createDeleteForm($id);
        $request = $this->getRequest();

        $form->submit($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('CyclearGameBundle:Transfer')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Transfer entity.');
            }
            
            $transferManager = $this->get('cyclear_game.manager.transfer');
            $res = $transferManager->revertTransfer($entity);
            if(!$res){
                $this->get('session')->getFlashBag()->add('error', 'Transfer kon niet verwijderd worden');
            }
            
            $em->flush();
        }

        return $this->redirect($this->generateUrl('admin_transfer'));
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
                ->add('id', 'hidden')
                ->getForm()
        ;
    }
}
