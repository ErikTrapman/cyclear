<?php declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Seizoen;
use App\Form\SeizoenType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Seizoen controller.
 */
#[Route(path: '/admin/seizoen')]
class SeizoenController extends AbstractController
{
    public function __construct(private readonly ManagerRegistry $doctrine)
    {
    }

    #[Route(path: '/', name: 'admin_seizoen')]
    public function indexAction(): \Symfony\Component\HttpFoundation\Response
    {
        $em = $this->doctrine->getManager();

        $entities = $em->getRepository(Seizoen::class)->findAll();

        return $this->render('admin/seizoen/index.html.twig', ['entities' => $entities]);
    }

    #[Route(path: '/new', name: 'admin_seizoen_new')]
    public function newAction(): \Symfony\Component\HttpFoundation\Response
    {
        $entity = new Seizoen();
        $form = $this->createForm(SeizoenType::class, $entity);

        return $this->render('admin/seizoen/new.html.twig', [
            'entity' => $entity,
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/create', name: 'admin_seizoen_create', methods: ['POST'])]
    public function createAction(Request $request): array|RedirectResponse
    {
        $entity = new Seizoen();
        $form = $this->createForm(SeizoenType::class, $entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->doctrine->getManager();
            $em->persist($entity);
            if ($entity->isCurrent()) {
                // find other seasons that are also current
                foreach ($em->getRepository(Seizoen::class)->findBy(['current' => true]) as $otherSeason) {
                    if ($otherSeason === $entity) {
                        continue;
                    }
                    $otherSeason->setCurrent(false);
                    $otherSeason->setClosed(true);
                }
            }
            $em->flush();
            return $this->redirect($this->generateUrl('admin_seizoen'));
        }
        return [
            'entity' => $entity,
            'form' => $form->createView(),
        ];
    }

    /**
     * @param mixed $id
     */
    #[Route(path: '/{id}/edit', name: 'admin_seizoen_edit')]
    public function editAction($id): \Symfony\Component\HttpFoundation\Response
    {
        $em = $this->doctrine->getManager();

        $entity = $em->getRepository(Seizoen::class)->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find entity.');
        }

        $editForm = $this->createForm(SeizoenType::class, $entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('admin/seizoen/edit.html.twig', [
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ]);
    }

    /**
     * @param mixed $id
     */
    #[Route(path: '/{id}/update', name: 'admin_seizoen_update', methods: ['POST'])]
    public function updateAction(Request $request, $id): array|RedirectResponse
    {
        $em = $this->doctrine->getManager();

        $entity = $em->getRepository(Seizoen::class)->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find entity.');
        }

        $editForm = $this->createForm(SeizoenType::class, $entity);
        $deleteForm = $this->createDeleteForm($id);

        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('admin_seizoen_edit', ['id' => $id]));
        }

        return [
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ];
    }

    /**
     * Deletes a Seizoen entity.
     *
     * @param mixed $id
     */
    #[Route(path: '/{id}/delete', name: 'admin_seizoen_delete', methods: ['POST'])]
    public function deleteAction(Request $request, $id): RedirectResponse
    {
        $form = $this->createDeleteForm($id);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->doctrine->getManager();
            $entity = $em->getRepository(Seizoen::class)->find($id);

            if ($entity->isCurrent()) {
                $this->addFlash('error', 'Je kunt niet een `huidig` seizoen verwijderen.');
                return $this->redirect($this->generateUrl('admin_seizoen'));
            }

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('admin_seizoen'));
    }

    private function createDeleteForm($id): \Symfony\Component\Form\FormInterface
    {
        return $this->createFormBuilder(['id' => $id])
            ->add('id', HiddenType::class)
            ->getForm();
    }
}
