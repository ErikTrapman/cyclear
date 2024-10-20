<?php declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Ploeg;
use App\EntityManager\UserManager;
use App\Form\Filter\PloegFilterType;
use App\Form\PloegType;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Ploeg controller.
 */
#[Route(path: '/admin/ploeg')]
class PloegController extends AbstractController
{
    public function __construct(
        private readonly ManagerRegistry $doctrine,
        private readonly PaginatorInterface $paginator,
        private readonly UserManager $userManager,
    ) {
    }

    #[Route(path: '/', name: 'admin_ploeg')]
    public function indexAction(Request $request): \Symfony\Component\HttpFoundation\Response
    {
        $filter = $this->createForm(PloegFilterType::class);

        $em = $this->doctrine->getManager();
        $query = $em->createQuery("SELECT p FROM App\Entity\Ploeg p ORDER BY p.id DESC");

        $config = $em->getConfiguration();
        $config->addFilter('naam', "App\Filter\Ploeg\PloegNaamFilter");

        if ('POST' == $request->getMethod()) {
            $filter->handleRequest($request);
            // $data = $filter->get('user')->getData();
            if ($filter->isValid()) {
                if ($filter->get('naam')->getData()) {
                    $em->getFilters()->enable('naam')->setParameter('naam', $filter->get('naam')->getData(), Type::getType(Types::STRING)->getBindingType());
                }
            }
        }
        $entities = $this->paginator->paginate(
            $query, (int)$request->query->get('page', 1)/* page number */, 20/* limit per page */
        );
        return $this->render('admin/ploeg/index.html.twig', ['entities' => $entities, 'filter' => $filter->createView()]);
    }

    #[Route(path: '/new', name: 'admin_ploeg_new')]
    public function newAction(): \Symfony\Component\HttpFoundation\Response
    {
        $entity = new Ploeg();
        $form = $this->createForm(PloegType::class, $entity);

        return $this->render('admin/ploeg/new.html.twig', [
            'entity' => $entity,
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/create', name: 'admin_ploeg_create', methods: ['POST'])]
    public function createAction(Request $request): array|RedirectResponse
    {
        $entity = new Ploeg();
        $form = $this->createForm(PloegType::class, $entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->doctrine->getManager();
            $em->persist($entity);
            $em->flush();

            $this->handleUserManagement($entity, $form->get('user')->getData());

            return $this->redirect($this->generateUrl('admin_ploeg'));
        }

        return [
            'entity' => $entity,
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/{id}/edit', name: 'admin_ploeg_edit')]
    public function editAction($id): \Symfony\Component\HttpFoundation\Response
    {
        $em = $this->doctrine->getManager();

        $entity = $em->getRepository(Ploeg::class)->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Ploeg entity.');
        }

        $editForm = $this->createForm(PloegType::class, $entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('admin/ploeg/edit.html.twig', [
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ]);
    }

    #[Route(path: '/{id}/update', name: 'admin_ploeg_update', methods: ['POST'])]
    public function updateAction(Request $request, $id): \Symfony\Component\HttpFoundation\Response
    {
        $em = $this->doctrine->getManager();

        $entity = $em->getRepository(Ploeg::class)->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Ploeg entity.');
        }

        $editForm = $this->createForm(PloegType::class, $entity);
        $deleteForm = $this->createDeleteForm($id);

        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            $this->handleUserManagement($entity, $editForm->get('user')->getData());

            return $this->redirect($this->generateUrl('admin_ploeg'));
        }

        return $this->render('admin/ploeg/edit.html.twig', [
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ]);
    }

    /**
     * Deletes a Ploeg entity.
     *
     * @param mixed $id
     */
    #[Route(path: '/{id}/delete', name: 'admin_ploeg_delete', methods: ['POST'])]
    public function deleteAction(Request $request, $id): RedirectResponse
    {
        $form = $this->createDeleteForm($id);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->doctrine->getManager();
            $entity = $em->getRepository(Ploeg::class)->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Ploeg entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('admin_ploeg'));
    }

    private function createDeleteForm($id): \Symfony\Component\Form\FormInterface
    {
        return $this->createFormBuilder(['id' => $id])
            ->add('id', HiddenType::class)
            ->getForm();
    }

    /**
     * @param mixed|null $user
     * @return void
     */
    private function handleUserManagement(Ploeg $ploeg, $user = null)
    {
        if (!$user) {
            return;
        }
        $this->userManager->setOwnerAcl($user, $ploeg);
        $ploeg->setUser($user);
        $em = $this->doctrine->getManager();
        $em->flush();
    }
}
