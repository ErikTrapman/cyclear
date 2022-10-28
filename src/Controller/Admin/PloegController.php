<?php declare(strict_types=1);

/*
 * This file is part of the Cyclear-game package.
 *
 * (c) Erik Trapman <veggatron@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\Admin;

use App\Entity\Ploeg;
use App\EntityManager\UserManager;
use App\Form\Filter\PloegFilterType;
use App\Form\PloegType;
use Doctrine\DBAL\Types\Type;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\HttpFoundation\Request;

/**
 * Ploeg controller.
 *
 * @Route("/admin/ploeg")
 */
class PloegController extends AbstractController
{
    public static function getSubscribedServices()
    {
        return array_merge([
            'knp_paginator' => PaginatorInterface::class,
            'cyclear_game.manager.user' => UserManager::class, ],
            parent::getSubscribedServices());
    }

    /**
     * Lists all Ploeg entities.
     *
     * @Route("/", name="admin_ploeg")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $filter = $this->createForm(PloegFilterType::class);

        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery("SELECT p FROM App\Entity\Ploeg p ORDER BY p.id DESC");

        $config = $em->getConfiguration();
        $config->addFilter('naam', "App\Filter\Ploeg\PloegNaamFilter");

        if ($request->getMethod() == 'POST') {
            $filter->handleRequest($request);
            //$data = $filter->get('user')->getData();
            if ($filter->isValid()) {
                if ($filter->get('naam')->getData()) {
                    $em->getFilters()->enable('naam')->setParameter('naam', $filter->get('naam')->getData(), Type::getType(Type::STRING)->getBindingType());
                }
            }
        }
        $paginator = $this->get('knp_paginator');
        $entities = $paginator->paginate(
            $query, $request->query->get('page', 1)/* page number */, 20/* limit per page */
        );
        return ['entities' => $entities, 'filter' => $filter->createView()];
    }

    /**
     * Displays a form to create a new Ploeg entity.
     *
     * @Route("/new", name="admin_ploeg_new")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Ploeg();
        $form = $this->createForm(PloegType::class, $entity);

        return [
            'entity' => $entity,
            'form' => $form->createView(),
        ];
    }

    /**
     * Creates a new Ploeg entity.
     *
     * @Route("/create", name="admin_ploeg_create")
     * @Method("post")
     */
    public function createAction(Request $request)
    {
        $entity = new Ploeg();
        $form = $this->createForm(PloegType::class, $entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            $this->handleUserManagement($entity, $form->get('user')->getData());

            return $this->redirect($this->generateUrl('admin_ploeg'));
        }

        return [
            'entity' => $entity,
            'form' => $form->createView(),
        ];
    }

    /**
     * Displays a form to edit an existing Ploeg entity.
     *
     * @Route("/{id}/edit", name="admin_ploeg_edit")
     * @Template()
     * @param mixed $id
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository(Ploeg::class)->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Ploeg entity.');
        }

        $editForm = $this->createForm(PloegType::class, $entity);
        $deleteForm = $this->createDeleteForm($id);

        return [
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ];
    }

    /**
     * Edits an existing Ploeg entity.
     *
     * @Route("/{id}/update", name="admin_ploeg_update")
     * @Method("post")
     * @param mixed $id
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository(Ploeg::class)->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Ploeg entity.');
        }

        $editForm = $this->createForm(PloegType::class, $entity);
        $deleteForm = $this->createDeleteForm($id);

        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            $this->handleUserManagement($entity, $editForm->get('user')->getData());

            return $this->redirect($this->generateUrl('admin_ploeg'));
        }

        return [
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ];
    }

    /**
     * Deletes a Ploeg entity.
     *
     * @Route("/{id}/delete", name="admin_ploeg_delete")
     * @Method("post")
     * @param mixed $id
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository(Ploeg::class)->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Ploeg entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('admin_ploeg'));
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(['id' => $id])
            ->add('id', HiddenType::class)
            ->getForm();
    }

    private function handleUserManagement($ploeg, $user = null)
    {
        if (!$user) {
            return;
        }
        $usermanager = $this->get('cyclear_game.manager.user');
        $usermanager->setOwnerAcl($user, $ploeg);
        $ploeg->setUser($user);
        $em = $this->getDoctrine()->getManager();
        $em->flush();
    }
}
