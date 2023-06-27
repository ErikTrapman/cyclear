<?php declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Contract;
use App\Entity\Seizoen;
use App\Form\Admin\ContractType;
use App\Form\Filter\RennerIdFilterType;
use App\Repository\ContractRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Contract controller.
 */
#[Route(path: '/admin/contract')]
class ContractController extends AbstractController
{
    public function __construct(
        private readonly PaginatorInterface $paginator,
        private readonly ManagerRegistry $doctrine,
        private readonly ContractRepository $contractRepository,
    ) {
    }

    #[Route(path: '/', name: 'admin_contract')]
    public function indexAction(Request $request): \Symfony\Component\HttpFoundation\Response
    {
        $em = $this->doctrine->getManager();

        $filter = $this->createForm(RennerIdFilterType::class);
        $config = $em->getConfiguration();
        $config->addFilter('renner', "App\Filter\RennerIdFilter");
        $entities = $this->contractRepository->createQueryBuilder('c')->orderBy('c.id', 'DESC');
        if ($request->getMethod() == 'POST') {
            $filter->handleRequest($request);
            if ($filter->isValid()) {
                if ($filter->get('renner')->getData()) {
                    // $em->getFilters()->enable("renner")->setParameter(
                    //    "renner", $filter->get('renner')->getData(), Type::getType(Type::OBJECT)->getBindingType()
                    // );
                    $entities->andWhere('c.renner = :renner')->setParameter('renner', $filter->get('renner')->getData()->getId());
                }
            }
        }

        $pagination = $this->paginator->paginate(
            $entities, $request->query->get('page', 1), 20
        );

        return $this->render('Admin/Contract/index.html.twig', ['entities' => $pagination, 'filter' => $filter->createView()]);
    }

    #[Route(path: '/new', name: 'admin_contract_new')]
    public function newAction(): \Symfony\Component\HttpFoundation\Response
    {
        $entity = new Contract();
        $form = $this->createForm(ContractType::class, $entity);

        return $this->render('Admin/Contract/new.html.twig', [
            'entity' => $entity,
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/create', name: 'admin_contract_create', methods: ['POST'])]
    public function createAction(Request $request): array|\Symfony\Component\HttpFoundation\RedirectResponse
    {
        $entity = new Contract();
        $form = $this->createForm(ContractType::class, $entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->doctrine->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('admin_contract'));
        }

        return [
            'entity' => $entity,
            'form' => $form->createView(),
        ];
    }

    /**
     * @param mixed $id
     */
    #[Route(path: '/{id}/edit', name: 'admin_contract_edit')]
    public function editAction($id): \Symfony\Component\HttpFoundation\Response
    {
        $em = $this->doctrine->getManager();

        $entity = $em->getRepository(Contract::class)->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find entity.');
        }

        $seizoen = $em->getRepository(Seizoen::class)->getCurrent();
        $editForm = $this->createForm(ContractType::class, $entity, ['seizoen' => $seizoen]);

        return $this->render('Admin/Contract/edit.html.twig', [
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
        ]);
    }

    /**
     * @param mixed $id
     */
    #[Route(path: '/{id}/update', name: 'admin_contract_update', methods: ['POST'])]
    public function updateAction(Request $request, $id): \Symfony\Component\HttpFoundation\Response
    {
        $em = $this->doctrine->getManager();

        $entity = $em->getRepository(Contract::class)->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find entity.');
        }

        $seizoen = $em->getRepository(Seizoen::class)->getCurrent();
        $editForm = $this->createForm(ContractType::class, $entity, ['seizoen' => $seizoen]);

        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('admin_contract_edit', ['id' => $id]));
        }

        return $this->render('admin/contract/edit.html.twig', [
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
        ]);
    }
}
