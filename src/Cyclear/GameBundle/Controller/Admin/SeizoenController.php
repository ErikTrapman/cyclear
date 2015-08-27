<?php

/*
 * This file is part of the Cyclear-game package.
 *
 * (c) Erik Trapman <veggatron@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cyclear\GameBundle\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Cyclear\GameBundle\Entity\Seizoen;
use Cyclear\GameBundle\Form\SeizoenType;

/**
 * Seizoen controller.
 *
 * @Route("/admin/seizoen")
 */
class SeizoenController extends Controller
{
    /**
     * Lists all Seizoen entities.
     *
     * @Route("/", name="admin_seizoen")
     * @Template("CyclearGameBundle:Seizoen/Admin:index.html.twig")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('CyclearGameBundle:Seizoen')->findAll();
        
        return array('entities' => $entities);
    }


    /**
     * Displays a form to create a new Seizoen entity.
     *
     * @Route("/new", name="admin_seizoen_new")
     * @Template("CyclearGameBundle:Seizoen/Admin:new.html.twig")
     */
    public function newAction()
    {
        $entity = new Seizoen();
        $form   = $this->createForm(new SeizoenType(), $entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView()
        );
    }

    /**
     * Creates a new Seizoen entity.
     *
     * @Route("/create", name="admin_seizoen_create")
     * @Method("post")
     */
    public function createAction()
    {
        $entity  = new Seizoen();
        $request = $this->getRequest();
        $form    = $this->createForm(new SeizoenType(), $entity);
        $form->submit($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('admin_seizoen'));
            
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView()
        );
    }

    /**
     * Displays a form to edit an existing Seizoen entity.
     *
     * @Route("/{id}/edit", name="admin_seizoen_edit")
     * @Template("CyclearGameBundle:Seizoen/Admin:edit.html.twig")
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('CyclearGameBundle:Seizoen')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find entity.');
        }

        $editForm = $this->createForm(new SeizoenType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Edits an existing Seizoen entity.
     *
     * @Route("/{id}/update", name="admin_seizoen_update")
     * @Method("post")
     */
    public function updateAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('CyclearGameBundle:Seizoen')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find entity.');
        }

        $editForm   = $this->createForm(new SeizoenType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        $request = $this->getRequest();

        $editForm->submit($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('admin_seizoen_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Deletes a Seizoen entity.
     *
     * @Route("/{id}/delete", name="admin_seizoen_delete")
     * @Method("post")
     */
    public function deleteAction($id)
    {
        $form = $this->createDeleteForm($id);
        $request = $this->getRequest();

        $form->submit($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('CyclearGameBundle:Seizoen')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('admin_seizoen'));
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }
}
