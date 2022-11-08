<?php declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Renner;
use App\Form\Filter\RennerFilterType;
use App\Form\RennerType;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Exception\ValidatorException;

/**
 * Renner controller.
 *
 * @Route("/admin/renner")
 */
class RennerController extends AbstractController
{
    public static function getSubscribedServices()
    {
        return array_merge(['knp_paginator' => PaginatorInterface::class],
            parent::getSubscribedServices());
    }

    /**
     * Lists all Renner entities.
     *
     * @Route ("/", name="admin_renner")
     *
     * @Template ()
     *
     * @return (\Symfony\Component\Form\FormView|mixed)[]
     *
     * @psalm-return array{pagination: mixed, filter: \Symfony\Component\Form\FormView}
     */
    public function indexAction(Request $request): array
    {
        $em = $this->getDoctrine()->getManager();

        $query = $em->createQuery('SELECT r FROM App\Entity\Renner r ORDER BY r.id DESC');
        $filter = $this->createForm(RennerFilterType::class);
        $config = $em->getConfiguration();
        $config->addFilter('naam', "App\Filter\RennerNaamFilter");
        if ($request->getMethod() == 'POST') {
            $filter->handleRequest($request);
            if ($filter->isValid()) {
                if ($filter->get('naam')->getData()) {
                    $em->getFilters()->enable('naam')->setParameter('naam', $filter->get('naam')->getData(), Type::getType(Types::STRING)->getBindingType());
                }
            }
        }

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query, $request->query->get('page', 1)/* page number */, 20/* limit per page */
        );
        return ['pagination' => $pagination, 'filter' => $filter->createView()];
    }

    /**
     * @Route ("/{id}/edit", name="admin_renner_edit")
     *
     * @Template ()
     *
     * @return (Renner|\Symfony\Component\Form\FormView|mixed)[]
     *
     * @psalm-return array{entity: Renner, edit_form: \Symfony\Component\Form\FormView, delete_form: mixed}
     */
    public function editAction(Request $request, $id): array
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository(Renner::class)->findOneBy(['cqranking_id' => $id]);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Renner entity.');
        }

        $editForm = $this->createForm(RennerType::class, $entity);

        if ($request->getMethod() == 'POST') {
            $editForm->handleRequest($request);
            $em->persist($entity);
            $em->flush();
        }

        $deleteForm = $this->createDeleteForm($id);

        return ['entity' => $entity, 'edit_form' => $editForm->createView(), 'delete_form' => $deleteForm->createView()];
    }

    public function createDeleteForm($id): \Symfony\Component\Form\FormInterface
    {
        return $this->createFormBuilder(['id' => $id])->add('id', HiddenType::class)->getForm();
    }

    /**
     * @Route ("/new", name="admin_renner_new")
     *
     * @Template ()
     *
     * @return (Renner|\Symfony\Component\Form\FormView)[]
     *
     * @psalm-return array{entity: Renner, form: \Symfony\Component\Form\FormView}
     */
    public function newAction(): array
    {
        $entity = new Renner();
        $form = $this->createForm(RennerType::class, $entity);

        return ['entity' => $entity, 'form' => $form->createView()];
    }

    /**
     * @Route ("/create", name="admin_renner_create", methods={"POST"})
     *
     * @return (Renner|\Symfony\Component\Form\FormView)[]|\Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @psalm-return \Symfony\Component\HttpFoundation\RedirectResponse|array{entity: Renner, form: \Symfony\Component\Form\FormView}
     */
    public function createAction(Request $request): array|\Symfony\Component\HttpFoundation\RedirectResponse
    {
        $entity = new Renner();
        $form = $this->createForm(RennerType::class, $entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('admin_renner'));
        }

        return ['entity' => $entity, 'form' => $form->createView()];
    }

    /**
     * @Route ("/{id}/delete", name="admin_renner_delete", methods={"POST"})
     */
    public function deleteAction(Request $request, $id): \Symfony\Component\HttpFoundation\RedirectResponse
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $renner = $em->getRepository(Renner::class)->findOneByCQId($id);
            $em->remove($renner);
            $em->flush();

            return $this->redirect($this->generateUrl('admin_renner'));
        }
        throw new ValidatorException('Invalid delete form');
    }
}
