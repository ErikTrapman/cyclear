<?php declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Contract;
use App\Entity\Seizoen;
use App\Form\Admin\ContractType;
use App\Form\Filter\RennerIdFilterType;
use App\Repository\ContractRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Contract controller.
 *
 * @Route("/admin/contract")
 */
class ContractController extends AbstractController
{
    public function __construct(
        private readonly PaginatorInterface $paginator,
        private readonly ManagerRegistry $doctrine,
        private readonly ContractRepository $contractRepository,
    ){

    }

    /**
     * @Route ("/", name="admin_contract")
     * @Template ()
     */
    public function indexAction(Request $request): array
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

        return ['entities' => $pagination, 'filter' => $filter->createView()];
    }

    /**
     * Displays a form to create a new Contract entity.
     *
     * @Route ("/new", name="admin_contract_new")
     *
     * @Template ()
     *
     * @return (Contract|\Symfony\Component\Form\FormView)[]
     *
     * @psalm-return array{entity: Contract, form: \Symfony\Component\Form\FormView}
     */
    public function newAction(): array
    {
        $entity = new Contract();
        $form = $this->createForm(ContractType::class, $entity);

        return [
            'entity' => $entity,
            'form' => $form->createView(),
        ];
    }

    /**
     * Creates a new Contract entity.
     *
     * @Route ("/create", name="admin_contract_create", methods={"POST"})
     *
     * @return (Contract|\Symfony\Component\Form\FormView)[]|\Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @psalm-return \Symfony\Component\HttpFoundation\RedirectResponse|array{entity: Contract, form: \Symfony\Component\Form\FormView}
     */
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
     * Displays a form to edit an existing Contract entity.
     *
     * @Route ("/{id}/edit", name="admin_contract_edit")
     *
     * @Template ()
     *
     * @psalm-return array{entity: Contract, edit_form: \Symfony\Component\Form\FormView}
     * @param mixed $id
     * @return (Contract|\Symfony\Component\Form\FormView)[]
     */
    public function editAction($id): array
    {
        $em = $this->doctrine->getManager();

        $entity = $em->getRepository(Contract::class)->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find entity.');
        }

        $seizoen = $em->getRepository(Seizoen::class)->getCurrent();
        $editForm = $this->createForm(ContractType::class, $entity, ['seizoen' => $seizoen]);

        return [
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
        ];
    }

    /**
     * @Route("/{id}/update", name="admin_contract_update", methods={"POST"})
     * @Template("admin/contract/edit.html.twig")
     */
    public function updateAction(Request $request, $id)
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

        return [
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
        ];
    }
}
