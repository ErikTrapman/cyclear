<?php

namespace Cyclear\GameBundle\Controller\Admin;

use Monolog\Logger;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Cyclear\GameBundle\Entity\Periode;
use Cyclear\GameBundle\Form\PeriodeType;

/**
 * Periode controller.
 *
 * @Route("/admin/periode")
 */
class PeriodeController extends Controller
{
    /**
     * Lists all Periode entities.
     *
     * @Route("/", name="admin_periode")
     * @Template("CyclearGameBundle:Periode/Admin:index.html.twig")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('CyclearGameBundle:Periode')->findAll();
        
        return array('entities' => $entities);
    }

    /**
     * Displays a form to create a new Periode entity.
     *
     * @Route("/new", name="admin_periode_new")
     * @Template("CyclearGameBundle:Periode/Admin:new.html.twig")
     */
    public function newAction()
    {
        $entity = new Periode();
        $form   = $this->createForm(new PeriodeType(), $entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView()
        );
    }

    /**
     * Creates a new Periode entity.
     *
     * @Route("/create", name="admin_periode_create")
     * @Method("post")
     */
    public function createAction()
    {
        $entity  = new Periode();
        $request = $this->getRequest();
        $form    = $this->createForm(new PeriodeType(), $entity);
        $form->submit($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('admin_periode'));
            
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView()
        );
    }

    /**
     * Displays a form to edit an existing Periode entity.
     *
     * @Route("/{id}/edit", name="admin_periode_edit")
     * @Template("CyclearGameBundle:Periode/Admin:edit.html.twig")
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('CyclearGameBundle:Periode')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Periode entity.');
        }

        $editForm = $this->createForm(new PeriodeType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Edits an existing Periode entity.
     *
     * @Route("/{id}/update", name="admin_periode_update")
     * @Method("post")
     */
    public function updateAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('CyclearGameBundle:Periode')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Periode entity.');
        }

        $editForm   = $this->createForm(new PeriodeType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        $request = $this->getRequest();

        $editForm->submit($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('admin_periode_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Deletes a Periode entity.
     *
     * @Route("/{id}/delete", name="admin_periode_delete")
     * @Method("post")
     */
    public function deleteAction($id)
    {
        $form = $this->createDeleteForm($id);
        $request = $this->getRequest();

        $form->submit($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('CyclearGameBundle:Periode')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Periode entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('admin_periode'));
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }
}
