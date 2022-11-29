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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Ploeg controller.
 *
 * @Route("/admin/ploeg")
 */
class PloegController extends AbstractController
{
    public function __construct(
        private readonly ManagerRegistry $doctrine,
        private readonly PaginatorInterface $paginator,
        private readonly UserManager $userManager,
    ) {

    }

    /**
     * Lists all Ploeg entities.
     *
     * @Route ("/", name="admin_ploeg")
     *
     * @Template ()
     *
     * @return (\Symfony\Component\Form\FormView|mixed)[]
     *
     * @psalm-return array{entities: mixed, filter: \Symfony\Component\Form\FormView}
     */
    public function indexAction(Request $request): array
    {
        $filter = $this->createForm(PloegFilterType::class);

        $em = $this->doctrine->getManager();
        $query = $em->createQuery("SELECT p FROM App\Entity\Ploeg p ORDER BY p.id DESC");

        $config = $em->getConfiguration();
        $config->addFilter('naam', "App\Filter\Ploeg\PloegNaamFilter");

        if ($request->getMethod() == 'POST') {
            $filter->handleRequest($request);
            // $data = $filter->get('user')->getData();
            if ($filter->isValid()) {
                if ($filter->get('naam')->getData()) {
                    $em->getFilters()->enable('naam')->setParameter('naam', $filter->get('naam')->getData(), Type::getType(Types::STRING)->getBindingType());
                }
            }
        }
        $entities = $this->paginator->paginate(
            $query, $request->query->get('page', 1)/* page number */, 20/* limit per page */
        );
        return ['entities' => $entities, 'filter' => $filter->createView()];
    }

    /**
     * Displays a form to create a new Ploeg entity.
     *
     * @Route ("/new", name="admin_ploeg_new")
     *
     * @Template ()
     *
     * @return (Ploeg|\Symfony\Component\Form\FormView)[]
     *
     * @psalm-return array{entity: Ploeg, form: \Symfony\Component\Form\FormView}
     */
    public function newAction(): array
    {
        $entity = new Ploeg();
        $form = $this->createForm(PloegType::class, $entity);

        return [
            'entity' => $entity,
            'form' => $form->createView(),
        ];
    }

    /**
     * Creates a new Ploeg entity.
     *
     * @Route ("/create", name="admin_ploeg_create", methods={"POST"})
     *
     * @return (Ploeg|\Symfony\Component\Form\FormView)[]|\Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @psalm-return \Symfony\Component\HttpFoundation\RedirectResponse|array{entity: Ploeg, form: \Symfony\Component\Form\FormView}
     */
    public function createAction(Request $request): array|\Symfony\Component\HttpFoundation\RedirectResponse
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

    /**
     * Displays a form to edit an existing Ploeg entity.
     *
     * @Route ("/{id}/edit", name="admin_ploeg_edit")
     *
     * @Template ()
     *
     * @psalm-return array{entity: Ploeg, edit_form: \Symfony\Component\Form\FormView, delete_form: mixed}
     * @param mixed $id
     * @return (Ploeg|\Symfony\Component\Form\FormView|mixed)[]
     */
    public function editAction($id): array
    {
        $em = $this->doctrine->getManager();

        $entity = $em->getRepository(Ploeg::class)->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Ploeg entity.');
        }

        $editForm = $this->createForm(PloegType::class, $entity);
        $deleteForm = $this->createDeleteForm($id);

        return [
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ];
    }

    /**
     * @Route("/{id}/update", name="admin_ploeg_update", methods={"POST"})
     * @Template("admin/ploeg/edit.html.twig")
     */
    public function updateAction(Request $request, $id): array|RedirectResponse
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

        return [
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ];
    }

    /**
     * Deletes a Ploeg entity.
     *
     * @Route ("/{id}/delete", name="admin_ploeg_delete", methods={"POST"})
     * @param mixed $id
     */
    public function deleteAction(Request $request, $id): \Symfony\Component\HttpFoundation\RedirectResponse
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
