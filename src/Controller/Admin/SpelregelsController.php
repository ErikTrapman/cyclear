<?php declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Spelregels;
use App\Form\SpelregelsType;
use Doctrine\Persistence\ManagerRegistry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Spelregels controller.
 *
 * @Route("/admin/spelregels")
 */
class SpelregelsController extends AbstractController
{
    public function __construct(private readonly ManagerRegistry $doctrine)
    {
    }

    /**
     * @Route("/", name="admin_spelregels")
     * @Template()
     */
    public function indexAction(): array
    {
        $em = $this->doctrine->getManager();

        $entities = $em->getRepository(Spelregels::class)->findAll();

        return ['entities' => $entities];
    }

    /**
     * @Route("/new", name="admin_spelregels_new")
     * @Template()
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
     * @Route("/create", name="admin_spelregels_create", methods={"POST"})
     */
    public function createAction(Request $request): array|RedirectResponse
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
     * @Route("/{id}/edit", name="admin_spelregels_edit")
     * @Template()
     * @param mixed $id
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
     * @Route("/{id}/update", name="admin_spelregels_update", methods={"POST"})
     * @param mixed $id
     */
    public function updateAction(Request $request, $id): array|RedirectResponse
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
     * @Route("/{id}/delete", name="admin_spelregels_delete", methods={"POST"})
     * @param mixed $id
     */
    public function deleteAction(Request $request, $id): RedirectResponse
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
