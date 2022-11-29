<?php declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Wedstrijd;
use App\Form\WedstrijdType;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/wedstrijd")
 */
class WedstrijdController extends AbstractController
{
    public function __construct(
        private readonly PaginatorInterface $paginator,
        private readonly ManagerRegistry $doctrine,
    ) {
    }

    /**
     * @Route("/", name="admin_wedstrijd")
     * @Template()
     */
    public function indexAction(Request $request): array
    {
        $em = $this->doctrine->getManager();

        $query = $em->createQuery('SELECT w FROM App\Entity\Wedstrijd w ORDER BY w.id DESC');

        $pagination = $this->paginator->paginate(
            $query, $request->query->get('page', 1)/* page number */, 20/* limit per page */
        );
        return ['pagination' => $pagination];
    }

    /**
     * @Route("/new", name="admin_wedstrijd_new")
     * @Template()
     */
    public function newAction(): array
    {
        $entity = new Wedstrijd();
        $form = $this->createForm(WedstrijdType::class, $entity);

        return [
            'entity' => $entity,
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/create", name="admin_wedstrijd_create", methods={"POST"})
     */
    public function createAction(Request $request): array|RedirectResponse
    {
        $entity = new Wedstrijd();
        $form = $this->createForm(WedstrijdType::class, $entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->doctrine->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('admin_wedstrijd'));
        }

        return [
            'entity' => $entity,
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/{id}/edit", name="admin_wedstrijd_edit")
     * @Template()
     * @param mixed $id
     */
    public function editAction($id): array
    {
        $em = $this->doctrine->getManager();

        $entity = $em->getRepository(Wedstrijd::class)->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Wedstrijd entity.');
        }

        $editForm = $this->createForm(WedstrijdType::class, $entity);
        $deleteForm = $this->createDeleteForm($id);

        return [
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ];
    }

    /**
     * @Route("/{id}/update", name="admin_wedstrijd_update", methods={"POST"})
     * @param mixed $id
     */
    public function updateAction(Request $request, $id): array|RedirectResponse
    {
        $em = $this->doctrine->getManager();

        $entity = $em->getRepository(Wedstrijd::class)->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Wedstrijd entity.');
        }

        $editForm = $this->createForm(WedstrijdType::class, $entity);
        $deleteForm = $this->createDeleteForm($id);

        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('admin_wedstrijd_edit', ['id' => $id]));
        }

        return [
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ];
    }

    /**
     * Deletes a Periode entity.
     *
     * @Route("/{id}/delete", name="admin_wedstrijd_delete", methods={"POST"})
     * @param mixed $id
     */
    public function deleteAction(Request $request, $id): RedirectResponse
    {
        $form = $this->createDeleteForm($id);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->doctrine->getManager();
            $entity = $em->getRepository(Wedstrijd::class)->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Wedstrijd entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('admin_wedstrijd'));
    }

    private function createDeleteForm($id): \Symfony\Component\Form\FormInterface
    {
        return $this->createFormBuilder(['id' => $id])
            ->add('id', HiddenType::class)
            ->getForm();
    }
}
