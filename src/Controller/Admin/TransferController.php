<?php declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Seizoen;
use App\Entity\Transfer;
use App\EntityManager\TransferManager;
use App\Form\Admin\Transfer\TransferEditType;
use App\Form\Admin\Transfer\TransferType;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

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
            'cyclear_game.manager.transfer' => TransferManager::class,
        ],
            parent::getSubscribedServices());
    }

    /**
     * Lists all Transfer entities.
     *
     * @Route ("/", name="admin_transfer")
     *
     * @Template ()
     *
     * @psalm-return array{entities: mixed}
     */
    public function indexAction(Request $request): array
    {
        $em = $this->getDoctrine()->getManager();

        $query = $em->createQuery('SELECT t FROM App\Entity\Transfer t ORDER BY t.id DESC');
        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query, $request->query->get('page', 1)/* page number */, 20/* limit per page */
        );
        //$entities = $query->getResult();

        return ['entities' => $pagination];
    }

    /**
     * Displays a form to create a new Transfer entity.
     *
     * @Route ("/new", name="admin_transfer_new")
     *
     * @Template ()
     *
     * @psalm-return array<empty, empty>
     */
    public function newAction(): array
    {
        return [];
    }

    /**
     * Displays a form to create a new Transfer entity.
     *
     * @Route ("/new-draft", name="admin_transfer_new_draft")
     *
     * @Template ()
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|array
     *
     * @psalm-return \Symfony\Component\HttpFoundation\RedirectResponse|array{form: mixed}
     */
    public function newDraftAction(Request $request): array|\Symfony\Component\HttpFoundation\RedirectResponse
    {
        $entity = new Transfer();
        $entity->setTransferType(Transfer::DRAFTTRANSFER);
        $entity->setDatum(new \DateTime());
        $form = $this->getTransferForm($entity);
        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $form->get('datum')->getData()->setTime((int)date('H'), (int)date('i'), 0);
                $transferManager = $this->get('cyclear_game.manager.transfer');
                $transferManager->doDraftTransfer($entity);
                $this->getDoctrine()->getManager()->flush();
                return $this->redirect($this->generateUrl('admin_transfer'));
            }
        }
        return ['form' => $form->createView()];
    }

    /**
     * Displays a form to create a new Transfer entity.
     *
     * @Route ("/new-exchange", name="admin_transfer_new_exchange")
     *
     * @Template ()
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|array
     *
     * @psalm-return \Symfony\Component\HttpFoundation\RedirectResponse|array{form: mixed}
     */
    public function newExchangeAction(Request $request): array|\Symfony\Component\HttpFoundation\RedirectResponse
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
        return ['form' => $form->createView()];
    }

    private function getTransferForm(Transfer $entity): \Symfony\Component\Form\FormInterface
    {
        $seizoen = $this->getDoctrine()->getRepository(Seizoen::class)->getCurrent();
        $form = $this->createForm(TransferType::class, $entity, ['transfertype' => $entity->getTransferType(), 'seizoen' => $seizoen]);
        return $form;
    }

    /**
     * Displays a form to edit an existing Transfer entity.
     *
     * @Route ("/{id}/edit", name="admin_transfer_edit")
     *
     * @Template ()
     *
     * @return (Transfer|\Symfony\Component\Form\FormView|mixed)[]
     *
     * @psalm-return array{entity: Transfer, edit_form: \Symfony\Component\Form\FormView, delete_form: mixed}
     */
    public function editAction($id): array
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository(Transfer::class)->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Transfer entity.');
        }

        $editForm = $this->createForm(TransferEditType::class, $entity);
        $deleteForm = $this->createDeleteForm($id);
        return [
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ];
    }

    /**
     * Edits an existing Transfer entity.
     *
     * @Route ("/{id}/update", name="admin_transfer_update", methods={"POST"})
     *
     * @return (Transfer|\Symfony\Component\Form\FormView|mixed)[]|\Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @psalm-return \Symfony\Component\HttpFoundation\RedirectResponse|array{entity: Transfer, edit_form: \Symfony\Component\Form\FormView, delete_form: mixed}
     */
    public function updateAction(Request $request, $id): array|\Symfony\Component\HttpFoundation\RedirectResponse
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

            return $this->redirect($this->generateUrl('admin_transfer_edit', ['id' => $id]));
        }

        return [
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ];
    }

    /**
     * Deletes a Transfer entity.
     *
     * @Route ("/{id}/delete", name="admin_transfer_delete", methods={"POST"})
     */
    public function deleteAction(Request $request, $id): \Symfony\Component\HttpFoundation\RedirectResponse
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

    private function createDeleteForm($id): \Symfony\Component\Form\FormInterface
    {
        return $this->createFormBuilder(['id' => $id])
            ->add('id', HiddenType::class)
            ->getForm();
    }
}
