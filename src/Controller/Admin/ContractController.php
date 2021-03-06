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

use App\Entity\Contract;
use App\Entity\Seizoen;
use App\Form\Filter\RennerIdFilterType;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use App\Form\Admin\ContractType;
use Doctrine\DBAL\Types\Type;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

/**
 * Contract controller.
 *
 * @Route("/admin/contract")
 */
class ContractController extends AbstractController
{
    public static function getSubscribedServices()
    {
        return array_merge(['knp_paginator' => PaginatorInterface::class],
            parent::getSubscribedServices());
    }
    /**
     * Lists all Contract entities.
     *
     * @Route("/", name="admin_contract")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $filter = $this->createForm(RennerIdFilterType::class);
        $config = $em->getConfiguration();
        $config->addFilter("renner", "App\Filter\RennerIdFilter");
        $entities = $em->getRepository(Contract::class)->createQueryBuilder('c')->orderBy('c.id', 'DESC');
        if ($request->getMethod() == 'POST') {
            $filter->handleRequest($request);
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
            $entities, $request->query->get('page', 1), 20
        );

        return array('entities' => $pagination, 'filter' => $filter->createView());
    }

    /**
     * Displays a form to create a new Contract entity.
     *
     * @Route("/new", name="admin_contract_new")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Contract();
        $form = $this->createForm(ContractType::class, $entity);

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
    public function createAction(Request $request)
    {
        $entity = new Contract();
        $form = $this->createForm(ContractType::class, $entity);
        $form->handleRequest($request);

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
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository(Contract::class)->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find entity.');
        }

        $seizoen = $em->getRepository(Seizoen::class)->getCurrent();
        $editForm = $this->createForm(ContractType::class, $entity, array('seizoen' => $seizoen));

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
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository(Contract::class)->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find entity.');
        }

        $editForm = $this->createForm(ContractType::class, $entity);

        $editForm->handleRequest($request);

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