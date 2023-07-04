<?php declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Spelregels;
use App\Form\SpelregelsType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Spelregels controller.
 */
#[Route(path: '/admin/spelregels')]
class SpelregelsController extends AbstractController
{
    public function __construct(private readonly ManagerRegistry $doctrine)
    {
    }

    #[Route(path: '/', name: 'admin_spelregels')]
    public function indexAction(): \Symfony\Component\HttpFoundation\Response
    {
        $em = $this->doctrine->getManager();

        $entities = $em->getRepository(Spelregels::class)->findAll();

        return $this->render('admin/spelregels/index.html.twig', ['entities' => $entities]);
    }

    #[Route(path: '/new', name: 'admin_spelregels_new')]
    public function newAction(): \Symfony\Component\HttpFoundation\Response
    {
        $entity = new Spelregels();
        $form = $this->createForm(SpelregelsType::class, $entity);

        return $this->render('admin/spelregels/new.html.twig', [
            'entity' => $entity,
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/create', name: 'admin_spelregels_create', methods: ['POST'])]
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
     * @param mixed $id
     */
    #[Route(path: '/{id}/edit', name: 'admin_spelregels_edit')]
    public function editAction($id): \Symfony\Component\HttpFoundation\Response
    {
        $em = $this->doctrine->getManager();

        $entity = $em->getRepository(Spelregels::class)->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Spelregels entity.');
        }

        $editForm = $this->createForm(SpelregelsType::class, $entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('admin/spelregels/edit.html.twig', [
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ]);
    }

    /**
     * @param mixed $id
     */
    #[Route(path: '/{id}/update', name: 'admin_spelregels_update', methods: ['POST'])]
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
     * @param mixed $id
     */
    #[Route(path: '/{id}/delete', name: 'admin_spelregels_delete', methods: ['POST'])]
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
