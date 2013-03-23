<?php

namespace Cyclear\GameBundle\Controller\Admin;

use Monolog\Logger;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Cyclear\GameBundle\Entity\UitslagType;
use Cyclear\GameBundle\Form\UitslagTypeType;

/**
 * UitslagType controller.
 *
 * @Route("/admin/uitslagtype")
 */
class UitslagTypeController extends Controller
{
    /**
     * Lists all UitslagType entities.
     *
     * @Route("/", name="admin_uitslagtype")
     * @Template("CyclearGameBundle:UitslagType/Admin:index.html.twig")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('CyclearGameBundle:UitslagType')->findAll();
        
        return array('entities' => $entities);
    }


    /**
     * Displays a form to create a new UitslagType entity.
     *
     * @Route("/new", name="admin_uitslagtype_new")
     * @Template("CyclearGameBundle:UitslagType/Admin:new.html.twig")
     */
    public function newAction()
    {
        $entity = new UitslagType();
        $form   = $this->createForm(new UitslagTypeType(), $entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView()
        );
    }

    /**
     * Creates a new UitslagType entity.
     *
     * @Route("/create", name="admin_uitslagtype_create")
     * @Method("post")
     */
    public function createAction()
    {
        $entity  = new UitslagType();
        $request = $this->getRequest();
        $form    = $this->createForm(new UitslagTypeType(), $entity);
        $form->bindRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('admin_uitslagtype'));
            
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView()
        );
    }

    /**
     * Displays a form to edit an existing UitslagType entity.
     *
     * @Route("/{id}/edit", name="admin_uitslagtype_edit")
     * @Template("CyclearGameBundle:UitslagType/Admin:edit.html.twig")
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('CyclearGameBundle:UitslagType')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find UitslagType entity.');
        }

        $editForm = $this->createForm(new UitslagTypeType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Edits an existing UitslagType entity.
     *
     * @Route("/{id}/update", name="admin_uitslagtype_update")
     * @Method("post")
     */
    public function updateAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('CyclearGameBundle:UitslagType')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find UitslagType entity.');
        }

        $editForm   = $this->createForm(new UitslagTypeType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        $request = $this->getRequest();

        $editForm->bindRequest($request);
        
        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('admin_uitslagtype_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Deletes a UitslagType entity.
     *
     * @Route("/{id}/delete", name="admin_uitslagtype_delete")
     * @Method("post")
     */
    public function deleteAction($id)
    {
        $form = $this->createDeleteForm($id);
        $request = $this->getRequest();

        $form->bindRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('CyclearGameBundle:UitslagType')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find UitslagType entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('admin_uitslagtype'));
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }
}
