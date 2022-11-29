<?php declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Nieuws;
use App\Form\NieuwsType;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Nieuws controller.
 *
 * @Route("/admin/nieuws")
 */
class NieuwsController extends AbstractController
{
    public function __construct(
        private readonly PaginatorInterface $paginator,
        private readonly ManagerRegistry $doctrine,
    ) {
    }

    /**
     * @Route("/", name="admin_nieuws")
     * @Template()
     */
    public function indexAction(Request $request): array
    {
        $em = $this->doctrine->getManager();

        $entities = $em->getRepository(Nieuws::class)->createQueryBuilder('n')->orderBy('n.id', 'DESC');

        $pagination = $this->paginator->paginate(
            $entities, $request->query->get('page', 1), 20
        );

        return ['entities' => $pagination];
    }

    /**
     * @Route("/new", name="admin_nieuws_new")
     * @Template()
     */
    public function newAction(): array
    {
        $entity = new Nieuws();
        $form = $this->createForm(NieuwsType::class, $entity);

        return [
            'entity' => $entity,
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/create", name="admin_nieuws_create", methods={"POST"})
     */
    public function createAction(Request $request): array|\Symfony\Component\HttpFoundation\RedirectResponse
    {
        $entity = new Nieuws();
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

    /**
     * @Route("/{id}/edit", name="admin_nieuws_edit")
     * @Template()
     * @param mixed $id
     */
    public function editAction($id): array
    {
        $em = $this->doctrine->getManager();

        $entity = $em->getRepository(Nieuws::class)->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Nieuws entity.');
        }

        $editForm = $this->createForm(NieuwsType::class, $entity);
        $deleteForm = $this->createDeleteForm($id);

        return [
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ];
    }

    /**
     * @Route("/{id}/update", name="admin_nieuws_update", methods={"POST"})
     * @Template("admin/nieuws/edit.html.twig")
     * @param mixed $id
     */
    public function updateAction(Request $request, $id): array|\Symfony\Component\HttpFoundation\RedirectResponse
    {
        $em = $this->doctrine->getManager();

        $entity = $em->getRepository(Nieuws::class)->find($id);

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

        return [
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ];
    }

    /**
     * Deletes a Nieuws entity.
     *
     * @Route("/{id}/delete", name="admin_nieuws_delete", methods={"POST"})
     * @param mixed $id
     */
    public function deleteAction(Request $request, $id): \Symfony\Component\HttpFoundation\RedirectResponse
    {
        $form = $this->createDeleteForm($id);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->doctrine->getManager();
            $entity = $em->getRepository(Nieuws::class)->find($id);

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
