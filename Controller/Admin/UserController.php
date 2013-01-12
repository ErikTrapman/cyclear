<?php

namespace Cyclear\GameBundle\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Cyclear\GameBundle\Entity\User;
use Cyclear\GameBundle\Form\UserType;

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
     * @Template("CyclearGameBundle:User/Admin:index.html.twig")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entities = $em->getRepository('CyclearGameBundle:User')->findAll();

        return array('entities' => $entities);
    }

    /**
     * Displays a form to create a new Ploeg entity.
     *
     * @Route("/new", name="admin_user_new")
     * @Template("CyclearGameBundle:User/Admin:new.html.twig")
     */
    public function newAction()
    {
        //$form = $this->get('fos_user.registration.form.type');
        $form = $this->createForm('admin_user_new');

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
    public function createAction()
    {
        $request = $this->getRequest();

        $form = $this->createForm('admin_user_new');
        $userManager = $this->container->get('fos_user.user_manager');

        $user = $userManager->createUser();
        $user->setEnabled(true);

        $form->setData($user);
        // IMPORTANT. We vragen niet om een password in het formulier. Zet hier dus tenminste een wachtwoord!
        $user->setPlainPassword(uniqid());
        $form->bindRequest($request);
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
     * @Template("CyclearGameBundle:User/Admin:edit.html.twig")
     */
    public function editAction($id)
    {
        // TODO: rollen in formulier.
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('CyclearGameBundle:User')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }
        $editForm = $this->createForm('admin_user_edit', $entity, array('user' => $entity));

        return array(
            'entity' => $entity,
            'edit_form' => $editForm->createView()
            //'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Edits an existing User entity.
     *
     * @Route("/{id}/update", name="admin_user_update")
     * @Template("CyclearGameBundle:User/Admin:edit.html.twig")
     * @Method("post")
     */
    public function updateAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('CyclearGameBundle:User')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }
        $editForm = $this->createForm('admin_user_edit', $entity);
        $request = $this->getRequest();


        // http://symfony.com/doc/master/cookbook/form/form_collections.html - Ensuring the database persistence
        $originalPloegen = array();
        // Create an array of the current Tag objects in the database
        foreach ($entity->getPloeg() as $ploeg) {
            $originalPloegen[] = $ploeg;
        }

        $editForm->bindRequest($request);
        if ($editForm->isValid()) {

            $usermanager = $this->get('cyclear_game.manager.user');
            //$usermanager->updatePloegen($editForm, $entity);
            foreach ($entity->getPloeg() as $ploeg) {
                foreach ($originalPloegen as $key => $toDel) {
                    if ($toDel->getId() === $ploeg->getId()) {
                        unset($originalPloegen[$key]);
                    }
                }
                $usermanager->setOwnerAcl($entity, $ploeg);
                $ploeg->setUser($entity);
            }

            // remove the relationship between the tag and the Task
            foreach ($originalPloegen as $ploeg) {
                // remove the Task from the Tag
                $ploeg->setUser(null);
                $usermanager->unsetOwnerAcl($entity, $ploeg);

                // if it were a ManyToOne relationship, remove the relationship like this
                // $tag->setTask(null);

                $em->persist($ploeg);

                // if you wanted to delete the Tag entirely, you can also do that
                // $em->remove($tag);
            }

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
