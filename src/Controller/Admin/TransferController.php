<?php declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Transfer;
use App\EntityManager\TransferManager;
use App\Form\Admin\Transfer\TransferEditType;
use App\Form\Admin\Transfer\TransferType;
use App\Repository\SeizoenRepository;
use App\Repository\TransferRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/admin/transfer')]
class TransferController extends AbstractController
{
    public function __construct(
        private readonly PaginatorInterface $paginator,
        private readonly ManagerRegistry $doctrine,
        private readonly TransferManager $transferManager,
        private readonly SeizoenRepository $seizoenRepository,
        private readonly TransferRepository $transferRepository,
    ) {
    }

    #[Route(path: '/', name: 'admin_transfer')]
    public function indexAction(Request $request): \Symfony\Component\HttpFoundation\Response
    {
        $em = $this->doctrine->getManager();

        $query = $em->createQuery('SELECT t FROM App\Entity\Transfer t ORDER BY t.id DESC');
        $pagination = $this->paginator->paginate(
            $query, (int)$request->query->get('page', 1)/* page number */, 20/* limit per page */
        );
        // $entities = $query->getResult();

        return $this->render('admin/transfer/index.html.twig', ['entities' => $pagination]);
    }

    #[Route(path: '/new', name: 'admin_transfer_new')]
    public function newAction(): \Symfony\Component\HttpFoundation\Response
    {
        return $this->render('admin/transfer/new.html.twig');
    }

    #[Route(path: '/new-draft', name: 'admin_transfer_new_draft')]
    public function newDraftAction(Request $request): \Symfony\Component\HttpFoundation\Response
    {
        $entity = new Transfer();
        $entity->setTransferType(Transfer::DRAFTTRANSFER);
        $entity->setDatum(new \DateTime());
        $form = $this->getTransferForm($entity);
        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $form->get('datum')->getData()->setTime((int)date('H'), (int)date('i'), 0);
                $this->transferManager->doDraftTransfer($entity);
                $this->doctrine->getManager()->flush();
                return $this->redirect($this->generateUrl('admin_transfer'));
            }
        }
        return $this->render('admin/transfer/new_draft.html.twig', ['form' => $form->createView()]);
    }

    #[Route(path: '/new-exchange', name: 'admin_transfer_new_exchange')]
    public function newExchangeAction(Request $request): \Symfony\Component\HttpFoundation\Response
    {
        $entity = new Transfer();
        $entity->setTransferType(Transfer::ADMINTRANSFER);
        $entity->setDatum(new \DateTime());
        // $entity->setSeizoen($seizoen);
        $form = $this->getTransferForm($entity);
        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $form->get('datum')->getData()->setTime((int)date('H'), (int)date('i'), (int)date('s'));
                $this->transferManager->doExchangeTransfer(
                    $form->get('renner')->getData(), $form->get('renner2')->getData(), $form->get('datum')->getData(), $form->get('seizoen')->getData());
                $this->doctrine->getManager()->flush();
                return $this->redirect($this->generateUrl('admin_transfer'));
            }
        }
        return $this->render('admin/transfer/new_exchange.html.twig', ['form' => $form->createView()]);
    }

    private function getTransferForm(Transfer $entity): \Symfony\Component\Form\FormInterface
    {
        $seizoen = $this->seizoenRepository->getCurrent();
        $form = $this->createForm(TransferType::class, $entity, ['transfertype' => $entity->getTransferType(), 'seizoen' => $seizoen]);
        return $form;
    }


    #[Route(path: '/{id}/edit', name: 'admin_transfer_edit')]
    public function editAction($id): \Symfony\Component\HttpFoundation\Response
    {
        $entity = $this->transferRepository->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Transfer entity.');
        }

        $editForm = $this->createForm(TransferEditType::class, $entity);
        $deleteForm = $this->createDeleteForm($id);
        return $this->render('admin/transfer/edit.html.twig', [
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ]);
    }


    #[Route(path: '/{id}/update', name: 'admin_transfer_update', methods: ['POST'])]
    public function updateAction(Request $request, $id): array|RedirectResponse
    {
        $em = $this->doctrine->getManager();

        $entity = $this->transferRepository->find($id);

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

    #[Route(path: '/{id}/delete', name: 'admin_transfer_delete', methods: ['POST'])]
    public function deleteAction(Request $request, $id): RedirectResponse
    {
        $form = $this->createDeleteForm($id);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->doctrine->getManager();
            $entity = $this->transferRepository->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Transfer entity.');
            }

            $res = $this->transferManager->revertTransfer($entity);
            if (!$res) {
                $request->getSession()->getFlashBag()->add('error', 'Transfer kon niet verwijderd worden');
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
