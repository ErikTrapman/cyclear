<?php declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Periode;
use App\Form\PeriodeType;
use App\Repository\PeriodeRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/admin/periode')]
class PeriodeController extends AbstractController
{
    public function __construct(
        private readonly ManagerRegistry $doctrine,
        private readonly PaginatorInterface $paginator,
        private readonly PeriodeRepository $periodeRepository,
    ) {
    }

    #[Route(path: '/', name: 'admin_periode')]
    public function indexAction(Request $request): \Symfony\Component\HttpFoundation\Response
    {
        $entities = $this->periodeRepository->createQueryBuilder('p')->orderBy('p.eind', 'DESC');

        $entities = $this->paginator->paginate(
            $entities, (int)$request->query->get('page', 1)/* page number */, 20/* limit per page */
        );

        return $this->render('admin/periode/index.html.twig', ['entities' => $entities]);
    }

    #[Route(path: '/new', name: 'admin_periode_new')]
    public function newAction(): \Symfony\Component\HttpFoundation\Response
    {
        $entity = new Periode();
        $form = $this->createForm(PeriodeType::class, $entity);

        return $this->render('admin/periode/new.html.twig', [
            'entity' => $entity,
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/create', name: 'admin_periode_create', methods: ['POST'])]
    public function createAction(Request $request): array|RedirectResponse
    {
        $entity = new Periode();
        $form = $this->createForm(PeriodeType::class, $entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->doctrine->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('admin_periode'));
        }

        return [
            'entity' => $entity,
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/{id}/edit', name: 'admin_periode_edit')]
    public function editAction($id): \Symfony\Component\HttpFoundation\Response
    {
        $em = $this->doctrine->getManager();

        $entity = $em->getRepository(Periode::class)->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Periode entity.');
        }

        $editForm = $this->createForm(PeriodeType::class, $entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('admin/periode/edit.html.twig', [
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ]);
    }

    #[Route(path: '/{id}/update', name: 'admin_periode_update', methods: ['POST'])]
    public function updateAction(Request $request, $id): \Symfony\Component\HttpFoundation\Response
    {
        $em = $this->doctrine->getManager();

        $entity = $em->getRepository(Periode::class)->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Periode entity.');
        }

        $editForm = $this->createForm(PeriodeType::class, $entity);
        $deleteForm = $this->createDeleteForm($id);

        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('admin_periode_edit', ['id' => $id]));
        }

        return $this->render('admin/periode/edit.html.twig', [
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ]);
    }

    #[Route(path: '/{id}/delete', name: 'admin_periode_delete', methods: ['POST'])]
    public function deleteAction(Request $request, $id): RedirectResponse
    {
        $form = $this->createDeleteForm($id);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->doctrine->getManager();
            $entity = $em->getRepository(Periode::class)->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Periode entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('admin_periode'));
    }

    private function createDeleteForm($id): \Symfony\Component\Form\FormInterface
    {
        return $this->createFormBuilder(['id' => $id])
            ->add('id', HiddenType::class)
            ->getForm();
    }
}
