<?php declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\UitslagType;
use App\Form\UitslagTypeType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * UitslagType controller.
 */
#[Route(path: '/admin/uitslagtype')]
class UitslagTypeController extends AbstractController
{
    public function __construct(private readonly ManagerRegistry $doctrine)
    {
    }

    #[Route(path: '/', name: 'admin_uitslagtype')]
    public function indexAction(): \Symfony\Component\HttpFoundation\Response
    {
        $em = $this->doctrine->getManager();

        $entities = $em->getRepository(UitslagType::class)->findAll();

        return $this->render('Admin/Uitsla1_Type/index.html.twig', ['entities' => $entities]);
    }

    #[Route(path: '/new', name: 'admin_uitslagtype_new')]
    public function newAction(): \Symfony\Component\HttpFoundation\Response
    {
        $entity = new UitslagType();
        $form = $this->createForm(UitslagTypeType::class, $entity);

        return $this->render('Admin/Uitsla1_Type/new.html.twig', [
            'entity' => $entity,
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/create', name: 'admin_uitslagtype_create', methods: ['POST'])]
    public function createAction(Request $request): array|RedirectResponse
    {
        $entity = new UitslagType();
        $form = $this->createForm(UitslagTypeType::class, $entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->doctrine->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('admin_uitslagtype'));
        }

        return [
            'entity' => $entity,
            'form' => $form->createView(),
        ];
    }

    /**
     * @param mixed $id
     */
    #[Route(path: '/{id}/edit', name: 'admin_uitslagtype_edit')]
    public function editAction($id): \Symfony\Component\HttpFoundation\Response
    {
        $em = $this->doctrine->getManager();

        $entity = $em->getRepository(UitslagType::class)->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find UitslagType entity.');
        }

        $editForm = $this->createForm(UitslagTypeType::class, $entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('Admin/Uitsla1_Type/edit.html.twig', [
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ]);
    }

    /**
     * @param mixed $id
     */
    #[Route(path: '/{id}/update', name: 'admin_uitslagtype_update', methods: ['POST'])]
    public function updateAction(Request $request, $id): array|RedirectResponse
    {
        $em = $this->doctrine->getManager();

        $entity = $em->getRepository(UitslagType::class)->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find UitslagType entity.');
        }

        $editForm = $this->createForm(UitslagTypeType::class, $entity);
        $deleteForm = $this->createDeleteForm($id);

        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('admin_uitslagtype_edit', ['id' => $id]));
        }

        return [
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ];
    }

    /**
     * Deletes a UitslagType entity.
     *
     * @param mixed $id
     */
    #[Route(path: '/{id}/delete', name: 'admin_uitslagtype_delete', methods: ['POST'])]
    public function deleteAction(Request $request, $id): RedirectResponse
    {
        $form = $this->createDeleteForm($id);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->doctrine->getManager();
            $entity = $em->getRepository(UitslagType::class)->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find UitslagType entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('admin_uitslagtype'));
    }

    private function createDeleteForm($id): \Symfony\Component\Form\FormInterface
    {
        return $this->createFormBuilder(['id' => $id])
            ->add('id', HiddenType::class)
            ->getForm();
    }
}
