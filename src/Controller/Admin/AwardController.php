<?php declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Award;
use App\Entity\AwardType;
use App\Form\AwardFormType;
use App\Repository\AwardRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/admin/award')]
class AwardController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly AwardRepository $awardRepository,
    ) {
    }

    #[Route(path: '/', name: 'admin_award')]
    public function indexAction(): Response
    {
        return $this->render('admin/award/index.html.twig', [
            'entities' => array_reverse($this->awardRepository->findAllWithPloeg()),
        ]);
    }

    #[Route(path: '/new', name: 'admin_award_new')]
    public function newAction(): Response
    {
        $form = $this->createForm(AwardFormType::class, new Award(null, AwardType::SeasonWinner));

        return $this->render('admin/award/new.html.twig', ['form' => $form->createView()]);
    }

    #[Route(path: '/create', name: 'admin_award_create', methods: ['POST'])]
    public function createAction(Request $request): Response|RedirectResponse
    {
        $entity = new Award(null, AwardType::SeasonWinner);
        $form = $this->createForm(AwardFormType::class, $entity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($entity);
            $this->em->flush();

            return $this->redirect($this->generateUrl('admin_award'));
        }

        return $this->render('admin/award/new.html.twig', ['form' => $form->createView()]);
    }

    #[Route(path: '/{id}/edit', name: 'admin_award_edit')]
    public function editAction(Award $entity): Response
    {
        return $this->render('admin/award/edit.html.twig', [
            'entity' => $entity,
            'edit_form' => $this->createForm(AwardFormType::class, $entity)->createView(),
            'delete_form' => $this->createDeleteForm($entity)->createView(),
        ]);
    }

    #[Route(path: '/{id}/update', name: 'admin_award_update', methods: ['POST'])]
    public function updateAction(Request $request, Award $entity): Response|RedirectResponse
    {
        $editForm = $this->createForm(AwardFormType::class, $entity);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->em->flush();

            return $this->redirect($this->generateUrl('admin_award_edit', ['id' => $entity->getId()]));
        }

        return $this->render('admin/award/edit.html.twig', [
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $this->createDeleteForm($entity)->createView(),
        ]);
    }

    #[Route(path: '/{id}/delete', name: 'admin_award_delete', methods: ['POST'])]
    public function deleteAction(Request $request, Award $entity): RedirectResponse
    {
        $form = $this->createDeleteForm($entity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->remove($entity);
            $this->em->flush();
        }

        return $this->redirect($this->generateUrl('admin_award'));
    }

    private function createDeleteForm(Award $entity): FormInterface
    {
        return $this->createFormBuilder(['id' => $entity->getId()])
            ->add('id', HiddenType::class)
            ->getForm();
    }
}
