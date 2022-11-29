<?php declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Seizoen;
use App\Form\SeizoenType;
use Doctrine\Persistence\ManagerRegistry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Seizoen controller.
 *
 * @Route("/admin/seizoen")
 */
class SeizoenController extends AbstractController
{
    public function __construct(private readonly ManagerRegistry $doctrine)
    {
    }

    /**
     * @Route("/", name="admin_seizoen")
     * @Template()
     */
    public function indexAction(): array
    {
        $em = $this->doctrine->getManager();

        $entities = $em->getRepository(Seizoen::class)->findAll();

        return ['entities' => $entities];
    }

    /**
     * @Route("/new", name="admin_seizoen_new")
     * @Template()
     */
    public function newAction(): array
    {
        $entity = new Seizoen();
        $form = $this->createForm(SeizoenType::class, $entity);

        return [
            'entity' => $entity,
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/create", name="admin_seizoen_create", methods={"POST"})
     */
    public function createAction(Request $request): array|RedirectResponse
    {
        $entity = new Seizoen();
        $form = $this->createForm(SeizoenType::class, $entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->doctrine->getManager();
            $em->persist($entity);
            if ($entity->isCurrent()) {
                // find other seasons that are also current
                foreach ($em->getRepository(Seizoen::class)->findBy(['current' => true]) as $otherSeason) {
                    if ($otherSeason === $entity) {
                        continue;
                    }
                    $otherSeason->setCurrent(false);
                    $otherSeason->setClosed(true);
                }
            }
            $em->flush();
            return $this->redirect($this->generateUrl('admin_seizoen'));
        }
        return [
            'entity' => $entity,
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/{id}/edit", name="admin_seizoen_edit")
     * @Template()
     * @param mixed $id
     */
    public function editAction($id): array
    {
        $em = $this->doctrine->getManager();

        $entity = $em->getRepository(Seizoen::class)->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find entity.');
        }

        $editForm = $this->createForm(SeizoenType::class, $entity);
        $deleteForm = $this->createDeleteForm($id);

        return [
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ];
    }

    /**
     * @Route("/{id}/update", name="admin_seizoen_update", methods={"POST"})
     * @param mixed $id
     */
    public function updateAction(Request $request, $id): array|RedirectResponse
    {
        $em = $this->doctrine->getManager();

        $entity = $em->getRepository(Seizoen::class)->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find entity.');
        }

        $editForm = $this->createForm(SeizoenType::class, $entity);
        $deleteForm = $this->createDeleteForm($id);

        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('admin_seizoen_edit', ['id' => $id]));
        }

        return [
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ];
    }

    /**
     * Deletes a Seizoen entity.
     *
     * @Route("/{id}/delete", name="admin_seizoen_delete", methods={"POST"})
     * @param mixed $id
     */
    public function deleteAction(Request $request, $id): RedirectResponse
    {
        $form = $this->createDeleteForm($id);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->doctrine->getManager();
            $entity = $em->getRepository(Seizoen::class)->find($id);

            if ($entity->isCurrent()) {
                $this->addFlash('error', 'Je kunt niet een `huidig` seizoen verwijderen.');
                return $this->redirect($this->generateUrl('admin_seizoen'));
            }

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('admin_seizoen'));
    }

    private function createDeleteForm($id): \Symfony\Component\Form\FormInterface
    {
        return $this->createFormBuilder(['id' => $id])
            ->add('id', HiddenType::class)
            ->getForm();
    }
}
