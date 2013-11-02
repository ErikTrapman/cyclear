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

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Cyclear\GameBundle\Form\Admin\ContractType;
use Doctrine\DBAL\Types\Type;

/**
 * Contract controller.
 *
 * @Route("/admin/contract")
 */
class ContractController extends \Symfony\Bundle\FrameworkBundle\Controller\Controller
{

    /**
     * Lists all Contract entities.
     *
     * @Route("/", name="admin_contract")
     * @Template("CyclearGameBundle:Contract/Admin:index.html.twig")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $filter = $this->createForm('renner_id_filter');
        $config = $em->getConfiguration();
        $config->addFilter("renner", "Cyclear\GameBundle\Filter\RennerIdFilter");
        $entities = $em->getRepository('CyclearGameBundle:Contract')->createQueryBuilder('c')->orderBy('c.id', 'DESC');
        if ($this->getRequest()->getMethod() == 'POST') {
            $filter->submit($this->getRequest());
            if ($filter->isValid()) {
                if ($filter->get('renner')->getData()) {
                    //$em->getFilters()->enable("renner")->setParameter(
                    //    "renner", $filter->get('renner')->getData(), Type::getType(Type::OBJECT)->getBindingType()
                    //);
                    $entities->andWhere('c.renner = :renner')->setParameter('renner', $filter->get('renner')->getData()->getId());
                }
            }
        }

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $entities, $this->get('request')->query->get('page', 1), 20
        );

        return array('entities' => $pagination, 'filter' => $filter->createView());
    }

    /**
     * Displays a form to create a new Contract entity.
     *
     * @Route("/new", name="admin_contract_new")
     * @Template("CyclearGameBundle:Contract/Admin:new.html.twig")
     */
    public function newAction()
    {
        $entity = new Contract();
        $form = $this->createForm(new ContractType(), $entity);

        return array(
            'entity' => $entity,
            'form' => $form->createView()
        );
    }

    /**
     * Creates a new Contract entity.
     *
     * @Route("/create", name="admin_contract_create")
     * @Method("post")
     */
    public function createAction()
    {
        $entity = new Contract();
        $request = $this->getRequest();
        $form = $this->createForm(new ContractType(), $entity);
        $form->submit($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('admin_contract'));
        }

        return array(
            'entity' => $entity,
            'form' => $form->createView()
        );
    }

    /**
     * Displays a form to edit an existing Contract entity.
     *
     * @Route("/{id}/edit", name="admin_contract_edit")
     * @Template("CyclearGameBundle:Contract/Admin:edit.html.twig")
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('CyclearGameBundle:Contract')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find entity.');
        }
        $seizoen = $this->getRequest()->attributes->get('seizoen-object');
        $editForm = $this->createForm(new ContractType(), $entity, array('seizoen' => $seizoen));

        return array(
            'entity' => $entity,
            'edit_form' => $editForm->createView()
        );
    }

    /**
     * Edits an existing Contract entity.
     *
     * @Route("/{id}/update", name="admin_contract_update")
     * @Method("post")
     */
    public function updateAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('CyclearGameBundle:Contract')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find entity.');
        }

        $editForm = $this->createForm(new ContractType(), $entity);

        $request = $this->getRequest();

        $editForm->submit($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('admin_contract_edit', array('id' => $id)));
        }

        return array(
            'entity' => $entity,
            'edit_form' => $editForm->createView()
        );
    }
}