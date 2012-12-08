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
     * Finds and displays a User entity.
     *
     * @Route("/{id}/show", name="admin_user_show")
     * @Template("CyclearGameBundle:User/Admin:show.html.twig")
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('CyclearGameBundle:User')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        return array(
            'entity'      => $entity,
        );
    }
    
    /**
     * Displays a form to create a new Ploeg entity.
     *
     * @Route("/new", name="admin_user_new")
     * @Template("CyclearGameBundle:User/Admin:new.html.twig")
     */
    public function newAction() {
        $form = $this->get('fos_user.registration.form');
        //$form = $this->createForm($this->get('fos_user.registration.form'), $entity);

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
    public function createAction() {
        
        $form = $this->get('fos_user.registration.form');
        $request = $this->getRequest();
        $form->bindRequest($request);

        if ($form->isValid()) {

            return $this->redirect($this->generateUrl('admin_user'));
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
        // TODO: ACL voor ploegen, rollen in formulier.
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('CyclearGameBundle:User')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        //$editForm = $this->get('fos_user.profile.form');
        $editForm = $this->createForm('admin_user_edit', $entity);
        //$deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView()
            //'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Edits an existing User entity.
     *
     * @Route("/{id}/update", name="admin_user_update")
     * @Method("post")
     */
    public function updateAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('CyclearGameBundle:User')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }
        
        $editForm   = $this->createForm(new UserType(), $entity);

        $request = $this->getRequest();

        $editForm->bindRequest($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('admin_user_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView()
        );
    }

}
