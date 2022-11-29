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
     * @Route("/", name="admin_ploeg")
     * @Template()
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
     * @Route("/new", name="admin_ploeg_new")
     * @Template()
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
     * @Route("/create", name="admin_ploeg_create", methods={"POST"})
     */
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

    /**
     * @Route("/{id}/edit", name="admin_ploeg_edit")
     * @Template()
     * @param mixed $id
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
     * @param mixed $id
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
     * @Route("/{id}/delete", name="admin_ploeg_delete", methods={"POST"})
     * @param mixed $id
     */
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
