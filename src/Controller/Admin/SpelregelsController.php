<?php declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Spelregels;
use App\Form\SpelregelsType;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Spelregels controller.
 *
 * @Route("/admin/spelregels")
 */
class SpelregelsController extends AbstractController
{
    public function __construct(private readonly ManagerRegistry $doctrine){

    }

    /**
     * Lists all Spelregels entities.
     *
     * @Route ("/", name="admin_spelregels")
     *
     * @Template ()
     *
     * @return Spelregels[][]
     *
     * @psalm-return array{entities: array<Spelregels>}
     */
    public function indexAction(): array
    {
        $em = $this->doctrine->getManager();

        $entities = $em->getRepository(Spelregels::class)->findAll();

        return ['entities' => $entities];
    }

    /**
     * Displays a form to create a new Spelregels entity.
     *
     * @Route ("/new", name="admin_spelregels_new")
     *
     * @Template ()
     *
     * @return (Spelregels|\Symfony\Component\Form\FormView)[]
     *
     * @psalm-return array{entity: Spelregels, form: \Symfony\Component\Form\FormView}
     */
    public function newAction(): array
    {
        $entity = new Spelregels();
        $form = $this->createForm(SpelregelsType::class, $entity);

        return [
            'entity' => $entity,
            'form' => $form->createView(),
        ];
    }

    /**
     * Creates a new Spelregels entity.
     *
     * @Route ("/create", name="admin_spelregels_create", methods={"POST"})
     *
     * @return (Spelregels|\Symfony\Component\Form\FormView)[]|\Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @psalm-return \Symfony\Component\HttpFoundation\RedirectResponse|array{entity: Spelregels, form: \Symfony\Component\Form\FormView}
     */
    public function createAction(Request $request): array|\Symfony\Component\HttpFoundation\RedirectResponse
    {
        $entity = new Spelregels();
        $form = $this->createForm(SpelregelsType::class, $entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->doctrine->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('admin_spelregels'));
        }

        return [
            'entity' => $entity,
            'form' => $form->createView(),
        ];
    }

    /**
     * Displays a form to edit an existing Spelregels entity.
     *
     * @Route ("/{id}/edit", name="admin_spelregels_edit")
     *
     * @Template ()
     *
     * @psalm-return array{entity: Spelregels, edit_form: \Symfony\Component\Form\FormView, delete_form: mixed}
     * @param mixed $id
     * @return (Spelregels|\Symfony\Component\Form\FormView|mixed)[]
     */
    public function editAction($id): array
    {
        $em = $this->doctrine->getManager();

        $entity = $em->getRepository(Spelregels::class)->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Spelregels entity.');
        }

        $editForm = $this->createForm(SpelregelsType::class, $entity);
        $deleteForm = $this->createDeleteForm($id);

        return [
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ];
    }

    /**
     * Edits an existing Spelregels entity.
     *
     * @Route ("/{id}/update", name="admin_spelregels_update", methods={"POST"})
     *
     * @psalm-return \Symfony\Component\HttpFoundation\RedirectResponse|array{entity: Spelregels, edit_form: \Symfony\Component\Form\FormView, delete_form: mixed}
     * @param mixed $id
     * @return (Spelregels|\Symfony\Component\Form\FormView|mixed)[]|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function updateAction(Request $request, $id): array|\Symfony\Component\HttpFoundation\RedirectResponse
    {
        $em = $this->doctrine->getManager();

        $entity = $em->getRepository(Spelregels::class)->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Spelregels entity.');
        }

        $editForm = $this->createForm(SpelregelsType::class, $entity);
        $deleteForm = $this->createDeleteForm($id);

        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('admin_spelregels_edit', ['id' => $id]));
        }

        return [
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ];
    }

    /**
     * Deletes a Spelregels entity.
     *
     * @Route ("/{id}/delete", name="admin_spelregels_delete", methods={"POST"})
     * @param mixed $id
     */
    public function deleteAction(Request $request, $id): \Symfony\Component\HttpFoundation\RedirectResponse
    {
        $form = $this->createDeleteForm($id);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->doctrine->getManager();
            $entity = $em->getRepository(Spelregels::class)->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Spelregels entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('admin_spelregels'));
    }

    private function createDeleteForm($id): \Symfony\Component\Form\FormInterface
    {
        return $this->createFormBuilder(['id' => $id])
            ->add('id', HiddenType::class)
            ->getForm();
    }
}
