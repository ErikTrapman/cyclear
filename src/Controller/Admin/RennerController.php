<?php declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Renner;
use App\Form\Filter\RennerFilterType;
use App\Form\RennerType;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Exception\ValidatorException;

/**
 * Renner controller.
 */
#[Route(path: '/admin/renner')]
class RennerController extends AbstractController
{
    public function __construct(
        private readonly PaginatorInterface $paginator,
        private readonly ManagerRegistry $doctrine,
    ) {
    }

    #[Route(path: '/', name: 'admin_renner')]
    public function indexAction(Request $request): \Symfony\Component\HttpFoundation\Response
    {
        $em = $this->doctrine->getManager();

        $query = $em->createQuery('SELECT r FROM App\Entity\Renner r ORDER BY r.id DESC');
        $filter = $this->createForm(RennerFilterType::class);
        $config = $em->getConfiguration();
        $config->addFilter('naam', "App\Filter\RennerNaamFilter");
        if ('POST' == $request->getMethod()) {
            $filter->handleRequest($request);
            if ($filter->isValid()) {
                if ($filter->get('naam')->getData()) {
                    $em->getFilters()->enable('naam')->setParameter('naam', $filter->get('naam')->getData(), Type::getType(Types::STRING)->getBindingType());
                }
            }
        }

        $pagination = $this->paginator->paginate(
            $query, $request->query->get('page', 1)/* page number */, 20/* limit per page */
        );
        return $this->render('admin/renner/index.html.twig', ['pagination' => $pagination, 'filter' => $filter->createView()]);
    }

    /**
     * @param mixed $id
     */
    #[Route(path: '/{id}/edit', name: 'admin_renner_edit')]
    public function editAction(Request $request, $id): \Symfony\Component\HttpFoundation\Response
    {
        $em = $this->doctrine->getManager();

        $entity = $em->getRepository(Renner::class)->findOneBy(['cqranking_id' => $id]);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Renner entity.');
        }

        $editForm = $this->createForm(RennerType::class, $entity);

        if ('POST' == $request->getMethod()) {
            $editForm->handleRequest($request);
            $em->persist($entity);
            $em->flush();
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('admin/renner/edit.html.twig', ['entity' => $entity, 'edit_form' => $editForm->createView(), 'delete_form' => $deleteForm->createView()]);
    }

    public function createDeleteForm($id): \Symfony\Component\Form\FormInterface
    {
        return $this->createFormBuilder(['id' => $id])->add('id', HiddenType::class)->getForm();
    }

    #[Route(path: '/new', name: 'admin_renner_new')]
    public function newAction(): \Symfony\Component\HttpFoundation\Response
    {
        $entity = new Renner();
        $form = $this->createForm(RennerType::class, $entity);

        return $this->render('admin/renner/new.html.twig', ['entity' => $entity, 'form' => $form->createView()]);
    }

    #[Route(path: '/create', name: 'admin_renner_create', methods: ['POST'])]
    public function createAction(Request $request): array|RedirectResponse
    {
        $entity = new Renner();
        $form = $this->createForm(RennerType::class, $entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->doctrine->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('admin_renner'));
        }

        return ['entity' => $entity, 'form' => $form->createView()];
    }

    /**
     * @param mixed $id
     */
    #[Route(path: '/{id}/delete', name: 'admin_renner_delete', methods: ['POST'])]
    public function deleteAction(Request $request, $id): RedirectResponse
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $em = $this->doctrine->getManager();
            $renner = $em->getRepository(Renner::class)->findOneByCQId($id);
            $em->remove($renner);
            $em->flush();

            return $this->redirect($this->generateUrl('admin_renner'));
        }
        throw new ValidatorException('Invalid delete form');
    }
}
