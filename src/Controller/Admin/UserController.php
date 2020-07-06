<?php

/*
 * This file is part of the Cyclear-game package.
 *
 * (c) Erik Trapman <veggatron@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\Admin;

use App\Form\UserEditType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use App\Entity\User;
use App\Form\UserType;
use Symfony\Component\HttpFoundation\Request;

/**
 * User controller.
 *
 * @Route("/admin/user")
 */
class UserController extends Controller
{

    /**
     * Lists all User entities.
     *
     * @Route("/", name="admin_user")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository(User::class)->findAll();

        return array('entities' => $entities);
    }

    /**
     * Displays a form to create a new Ploeg entity.
     *
     * @Route("/new", name="admin_user_new")
     * @Template()
     */
    public function newAction()
    {
        $form = $this->createForm(UserType::class);

        return array(
            'form' => $form->createView()
        );
    }

    /**
     * Creates a new User entity.
     *
     * @Route("/create", name="admin_user_create")
     * @Method("post")
     */
    public function createAction(Request $request)
    {
        $form = $this->createForm(UserType::class);
        $userManager = $this->container->get('fos_user.user_manager');

        $user = $userManager->createUser();
        $user->setEnabled(true);

        $form->setData($user);
        // IMPORTANT. We vragen niet om een password in het formulier. Zet hier dus tenminste een wachtwoord!
        $user->setPlainPassword(uniqid());
        $form->handleRequest($request);
        if ($form->isValid()) {
            $userManager->updateUser($user);
            return $this->redirect($this->generateUrl('admin_user_edit', array('id' => $user->getId())));
        }

        return array(
            'form' => $form->createView()
        );
    }

    /**
     * Displays a form to edit an existing User entity.
     *
     * @Route("/{id}/edit", name="admin_user_edit")
     * @Template()
     */
    public function editAction($id)
    {
        // TODO: rollen in formulier.
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository(User::class)->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }
        $editForm = $this->createForm(UserEditType::class, $entity);

        return array(
            'entity' => $entity,
            'edit_form' => $editForm->createView()
        );
    }

    /**
     * Edits an existing User entity.
     *
     * @Route("/{id}/update", name="admin_user_update")
     * @Template()
     * @Method("post")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

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

            return $this->redirect($this->generateUrl('admin_user_edit', array('id' => $id)));
        }

        return array(
            'entity' => $entity,
            'edit_form' => $editForm->createView()
        );
    }
}
