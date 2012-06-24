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

}
