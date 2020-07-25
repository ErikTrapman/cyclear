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

use App\Entity\Seizoen;
use App\EntityManager\TransferManager;
use App\Form\Admin\Transfer\TransferEditType;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use App\Entity\Transfer;
use App\Form\Admin\Transfer\TransferType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\HttpFoundation\Request;

/**
 * Transfer controller.
 *
 * @Route("/admin/transfer")
 */
class TransferController extends AbstractController
{
    public static function getSubscribedServices()
    {
        return array_merge([
            'knp_paginator' => PaginatorInterface::class,
            'cyclear_game.manager.transfer' => TransferManager::class
        ],
            parent::getSubscribedServices());
    }
    /**
     * Lists all Transfer entities.
     *
     * @Route("/", name="admin_transfer")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $query = $em->createQuery('SELECT t FROM App\Entity\Transfer t ORDER BY t.id DESC');
        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query, $request->query->get('page', 1)/* page number */, 20/* limit per page */
        );
        //$entities = $query->getResult();

        return array('entities' => $pagination);
    }

    /**
     * Displays a form to create a new Transfer entity.
     *
     * @Route("/new", name="admin_transfer_new")
     * @Template()
     */
    public function newAction()
    {
        return array();
    }

    /**
     * Displays a form to create a new Transfer entity.
     *
     * @Route("/new-draft", name="admin_transfer_new_draft")
     * @Template("admin/transfer/newDraft.html.twig")
     */
    public function newDraftAction(\Symfony\Component\HttpFoundation\Request $request)
    {
        $entity = new Transfer();
        $entity->setTransferType(Transfer::DRAFTTRANSFER);
        $entity->setDatum(new \DateTime());
        $form = $this->getTransferForm($entity);
        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);
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
     * @Template("admin/transfer/newExchange.html.twig")
     */
    public function newExchangeAction(\Symfony\Component\HttpFoundation\Request $request)
    {
        $entity = new Transfer();
        $entity->setTransferType(Transfer::ADMINTRANSFER);
        $entity->setDatum(new \DateTime());
        //$entity->setSeizoen($seizoen);
        $form = $this->getTransferForm($entity);
        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);
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
        $seizoen = $this->getDoctrine()->getRepository(Seizoen::class)->getCurrent();
        $form = $this->createForm(TransferType::class, $entity, array('transfertype' => $entity->getTransferType(), 'seizoen' => $seizoen));
        return $form;
    }

    /**
     * Displays a form to edit an existing Transfer entity.
     *
     * @Route("/{id}/edit", name="admin_transfer_edit")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository(Transfer::class)->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Transfer entity.');
        }

        $editForm = $this->createForm(TransferEditType::class, $entity);
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
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository(Transfer::class)->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Transfer entity.');
        }

        $editForm = $this->createForm(TransferEditType::class, $entity);
        $deleteForm = $this->createDeleteForm($id);

        $editForm->handleRequest($request);

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
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository(Transfer::class)->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Transfer entity.');
            }

            $transferManager = $this->get('cyclear_game.manager.transfer');
            $res = $transferManager->revertTransfer($entity);
            if (!$res) {
                $this->get('session')->getFlashBag()->add('error', 'Transfer kon niet verwijderd worden');
            }

            $em->flush();
        }

        return $this->redirect($this->generateUrl('admin_transfer'));
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', HiddenType::class)
            ->getForm();
    }
}
