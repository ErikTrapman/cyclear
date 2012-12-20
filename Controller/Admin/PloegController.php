<?php

namespace Cyclear\GameBundle\Controller\Admin;

use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Cyclear\GameBundle\Entity\Ploeg;
use Cyclear\GameBundle\Form\PloegType;

/**
 * Ploeg controller.
 *
 * @Route("/admin/ploeg")
 */
class PloegController extends Controller {

    /**
     * Lists all Ploeg entities.
     *
     * @Route("/", name="admin_ploeg")
     * @Template("CyclearGameBundle:Ploeg/Admin:index.html.twig")
     */
    public function indexAction() {
        
        $filter = $this->createForm('ploeg_filter');

        $em = $this->getDoctrine()->getEntityManager();
        $query = $em->createQuery("SELECT p FROM Cyclear\GameBundle\Entity\Ploeg p");

        $config = $em->getConfiguration();
        $config->addFilter("naam", "Cyclear\GameBundle\Filter\Ploeg\PloegNaamFilter");
        $config->addFilter("user", "Cyclear\GameBundle\Filter\Ploeg\PloegUserFilter");

        if ($this->getRequest()->getMethod() == 'POST') {
            $filter->bindRequest($this->getRequest());
            //$data = $filter->get('user')->getData();
            if ($filter->isValid()) {
                if ($filter->get('naam')->getData()) {
                    $em->getFilters()->enable("naam")->setParameter("naam", $filter->get('naam')->getData(), \Doctrine\DBAL\Types\Type::getType(\Doctrine\DBAL\Types\Type::STRING)->getBindingType());
                }
                if ($filter->get('user')->getData()) {
                    $em->getFilters()->enable("user")->setParameter("user", $filter->get('user')->getData(), \Doctrine\DBAL\Types\Type::getType(\Doctrine\DBAL\Types\Type::STRING)->getBindingType());
                }
            }
        }
        //$entities = $em->getRepository('CyclearGameBundle:Ploeg')->findAll();
        $entities = $query->getResult();

        return array('entities' => $entities, 'filter' => $filter->createView());
    }

    /**
     * Finds and displays a Ploeg entity.
     *
     * @Route("/{id}/show", name="admin_ploeg_show")
     * @Template("CyclearGameBundle:Ploeg/Admin:show.html.twig")
     */
    public function showAction($id) {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('CyclearGameBundle:Ploeg')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Ploeg entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $renners = $em->getRepository('CyclearGameBundle:Ploeg')->getRenners($entity);
        return array(
            'entity' => $entity,
            'renners' => $renners,
            'delete_form' => $deleteForm->createView(),);
    }

    /**
     * Displays a form to create a new Ploeg entity.
     *
     * @Route("/new", name="admin_ploeg_new")
     * @Template("CyclearGameBundle:Ploeg/Admin:new.html.twig")
     */
    public function newAction() {
        $entity = new Ploeg();
        $form = $this->createForm(new PloegType(), $entity);

        return array(
            'entity' => $entity,
            'form' => $form->createView()
        );
    }

    /**
     * Creates a new Ploeg entity.
     *
     * @Route("/create", name="admin_ploeg_create")
     * @Method("post")
     */
    public function createAction() {
        $entity = new Ploeg();
        $request = $this->getRequest();
        $form = $this->createForm(new PloegType(), $entity);
        $form->bindRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('admin_ploeg_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form' => $form->createView()
        );
    }

    /**
     * Displays a form to edit an existing Ploeg entity.
     *
     * @Route("/{id}/edit", name="admin_ploeg_edit")
     * @Template("CyclearGameBundle:Ploeg/Admin:edit.html.twig")
     */
    public function editAction($id) {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('CyclearGameBundle:Ploeg')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Ploeg entity.');
        }

        $editForm = $this->createForm(new PloegType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Edits an existing Ploeg entity.
     *
     * @Route("/{id}/update", name="admin_ploeg_update")
     * @Method("post")
     */
    public function updateAction($id) {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('CyclearGameBundle:Ploeg')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Ploeg entity.');
        }

        $editForm = $this->createForm(new PloegType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        $request = $this->getRequest();

        $editForm->bindRequest($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('admin_ploeg_edit', array('id' => $id)));
        }

        return array(
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Deletes a Ploeg entity.
     *
     * @Route("/{id}/delete", name="admin_ploeg_delete")
     * @Method("post")
     */
    public function deleteAction($id) {
        $form = $this->createDeleteForm($id);
        $request = $this->getRequest();

        $form->bindRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $entity = $em->getRepository('CyclearGameBundle:Ploeg')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Ploeg entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('admin_ploeg'));
    }

    private function createDeleteForm($id) {
        return $this->createFormBuilder(array('id' => $id))
                        ->add('id', 'hidden')
                        ->getForm()
        ;
    }

}
