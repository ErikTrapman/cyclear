<?php

namespace Cyclear\GameBundle\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Cyclear\GameBundle\Entity\Spelregels;
use Cyclear\GameBundle\Form\SpelregelsType;

/**
 * Spelregels controller.
 *
 * @Route("/admin/spelregels")
 */
class SpelregelsController extends Controller
{
    /**
     * Lists all Spelregels entities.
     *
     * @Route("/", name="admin_spelregels")
     * @Template("CyclearGameBundle:Spelregels/Admin:index.html.twig")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('CyclearGameBundle:Spelregels')->findAll();
        
        return array('entities' => $entities);
    }

    /**
     * Displays a form to create a new Spelregels entity.
     *
     * @Route("/new", name="admin_spelregels_new")
     * @Template("CyclearGameBundle:Spelregels/Admin:new.html.twig")
     */
    public function newAction()
    {
        $entity = new Spelregels();
        $form   = $this->createForm(new SpelregelsType(), $entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView()
        );
    }

    /**
     * Creates a new Spelregels entity.
     *
     * @Route("/create", name="admin_spelregels_create")
     * @Method("post")
     */
    public function createAction()
    {
        $entity  = new Spelregels();
        $request = $this->getRequest();
        $form    = $this->createForm(new SpelregelsType(), $entity);
        $form->submit($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('admin_spelregels'));
            
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView()
        );
    }

    /**
     * Displays a form to edit an existing Spelregels entity.
     *
     * @Route("/{id}/edit", name="admin_spelregels_edit")
     * @Template("CyclearGameBundle:Spelregels/Admin:edit.html.twig")
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('CyclearGameBundle:Spelregels')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Spelregels entity.');
        }

        $editForm = $this->createForm(new SpelregelsType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Edits an existing Spelregels entity.
     *
     * @Route("/{id}/update", name="admin_spelregels_update")
     * @Method("post")
     */
    public function updateAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('CyclearGameBundle:Spelregels')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Spelregels entity.');
        }

        $editForm   = $this->createForm(new SpelregelsType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        $request = $this->getRequest();

        $editForm->submit($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('admin_spelregels_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Deletes a Spelregels entity.
     *
     * @Route("/{id}/delete", name="admin_spelregels_delete")
     * @Method("post")
     */
    public function deleteAction($id)
    {
        $form = $this->createDeleteForm($id);
        $request = $this->getRequest();

        $form->submit($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('CyclearGameBundle:Spelregels')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Spelregels entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('admin_spelregels'));
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }
}
