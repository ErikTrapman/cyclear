<?php declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\UserEditType;
use App\Form\UserType;
use Doctrine\Persistence\ManagerRegistry;
use FOS\UserBundle\Model\UserManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * User controller.
 */
#[Route(path: '/admin/user')]
class UserController extends AbstractController
{
    public function __construct(
        private readonly ManagerRegistry $doctrine,
        private readonly UserManagerInterface $userManager,
    ) {
    }

    #[Route(path: '/', name: 'admin_user')]
    public function indexAction(): \Symfony\Component\HttpFoundation\Response
    {
        $em = $this->doctrine->getManager();

        $entities = $em->getRepository(User::class)->findAll();

        return $this->render('Admin/User/index.html.twig', ['entities' => $entities]);
    }

    #[Route(path: '/new', name: 'admin_user_new')]
    public function newAction(): \Symfony\Component\HttpFoundation\Response
    {
        $form = $this->createForm(UserType::class);

        return $this->render('Admin/User/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/create', name: 'admin_user_create', methods: ['POST'])]
    public function createAction(Request $request): array|RedirectResponse
    {
        $form = $this->createForm(UserType::class);
        $userManager = $this->userManager;

        $user = $userManager->createUser();
        $user->setEnabled(true);

        $form->setData($user);
        // IMPORTANT. We vragen niet om een password in het formulier. Zet hier dus tenminste een wachtwoord!
        $user->setPlainPassword(uniqid());
        $form->handleRequest($request);
        if ($form->isValid()) {
            $userManager->updateUser($user);
            return $this->redirect($this->generateUrl('admin_user_edit', ['id' => $user->getId()]));
        }

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * @param mixed $id
     */
    #[Route(path: '/{id}/edit', name: 'admin_user_edit')]
    public function editAction($id): \Symfony\Component\HttpFoundation\Response
    {
        $em = $this->doctrine->getManager();

        $entity = $em->getRepository(User::class)->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }
        $editForm = $this->createForm(UserEditType::class, $entity);

        return $this->render('Admin/User/edit.html.twig', [
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
        ]);
    }

    /**
     * @param mixed $id
     */
    #[Route(path: '/{id}/update', name: 'admin_user_update', methods: ['POST'])]
    public function updateAction(Request $request, $id): \Symfony\Component\HttpFoundation\Response
    {
        $em = $this->doctrine->getManager();

        $entity = $em->getRepository(User::class)->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }
        $editForm = $this->createForm(UserEditType::class, $entity);

//        // http://symfony.com/doc/master/cookbook/form/form_collections.html - Ensuring the database persistence
//        $originalPloegen = array();
//        // Create an array of the current Tag objects in the database
//        foreach ($entity->getPloeg() as $ploeg) {
//            $originalPloegen[] = $ploeg;
//        }

        $editForm->handleRequest($request);
        if ($editForm->isValid()) {
//            this is now done in PloegController
//            $usermanager = $this->get('cyclear_game.manager.user');
//            //$usermanager->updatePloegen($editForm, $entity);
//            foreach ($entity->getPloeg() as $ploeg) {
//                foreach ($originalPloegen as $key => $toDel) {
//                    if ($toDel->getId() === $ploeg->getId()) {
//                        unset($originalPloegen[$key]);
//                    }
//                }
//                $usermanager->setOwnerAcl($entity, $ploeg);
//                $ploeg->setUser($entity);
//            }
//
//            // remove the relationship between the tag and the Task
//            foreach ($originalPloegen as $ploeg) {
//                // remove the Task from the Tag
//                $ploeg->setUser(null);
//                $usermanager->unsetOwnerAcl($entity, $ploeg);
//
//                // if it were a ManyToOne relationship, remove the relationship like this
//                // $tag->setTask(null);
//
//                $em->persist($ploeg);
//
//                // if you wanted to delete the Tag entirely, you can also do that
//                // $em->remove($tag);
//            }

            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('admin_user_edit', ['id' => $id]));
        }

        return $this->render('admin/user/edit.html.twig', [
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
        ]);
    }
}
