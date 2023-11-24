<?php declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\News;
use App\Form\NieuwsType;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/admin/news')]
class NewsController extends AbstractController
{
    public function __construct(
        private readonly PaginatorInterface $paginator,
        private readonly ManagerRegistry $doctrine,
    ) {
    }

    #[Route(path: '/', name: 'admin_nieuws')]
    public function indexAction(Request $request): \Symfony\Component\HttpFoundation\Response
    {
        $em = $this->doctrine->getManager();

        $entities = $em->getRepository(News::class)->createQueryBuilder('n')->orderBy('n.id', 'DESC');

        $pagination = $this->paginator->paginate(
            $entities, (int)$request->query->get('page', 1), 20
        );

        return $this->render('admin/news/index.html.twig', ['entities' => $pagination]);
    }

    #[Route(path: '/new', name: 'admin_nieuws_new')]
    public function newAction(): \Symfony\Component\HttpFoundation\Response
    {
        $entity = new News();
        $form = $this->createForm(NieuwsType::class, $entity);

        return $this->render('admin/news/new.html.twig', [
            'entity' => $entity,
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/create', name: 'admin_nieuws_create', methods: ['POST'])]
    public function createAction(Request $request): array|\Symfony\Component\HttpFoundation\RedirectResponse
    {
        $entity = new News();
        $form = $this->createForm(NieuwsType::class, $entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->doctrine->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('admin_nieuws'));
        }

        return [
            'entity' => $entity,
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/{id}/edit', name: 'admin_nieuws_edit')]
    public function editAction($id): \Symfony\Component\HttpFoundation\Response
    {
        $em = $this->doctrine->getManager();

        $entity = $em->getRepository(News::class)->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Nieuws entity.');
        }

        $editForm = $this->createForm(NieuwsType::class, $entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('admin/news/edit.html.twig', [
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ]);
    }

    #[Route(path: '/{id}/update', name: 'admin_nieuws_update', methods: ['POST'])]
    public function updateAction(Request $request, $id): \Symfony\Component\HttpFoundation\Response
    {
        $em = $this->doctrine->getManager();

        $entity = $em->getRepository(News::class)->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Nieuws entity.');
        }

        $editForm = $this->createForm(NieuwsType::class, $entity);
        $deleteForm = $this->createDeleteForm($id);

        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('admin_nieuws_edit', ['id' => $id]));
        }

        return $this->render('admin/nieuws/edit.html.twig', [
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ]);
    }

    #[Route(path: '/{id}/delete', name: 'admin_nieuws_delete', methods: ['POST'])]
    public function deleteAction(Request $request, $id): \Symfony\Component\HttpFoundation\RedirectResponse
    {
        $form = $this->createDeleteForm($id);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->doctrine->getManager();
            $entity = $em->getRepository(News::class)->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Nieuws entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('admin_nieuws'));
    }

    private function createDeleteForm($id): \Symfony\Component\Form\FormInterface
    {
        return $this->createFormBuilder(['id' => $id])
            ->add('id', HiddenType::class)
            ->getForm();
    }
}
