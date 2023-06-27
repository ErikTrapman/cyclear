<?php declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Seizoen;
use App\Entity\Uitslag;
use App\Entity\Wedstrijd;
use App\Form\UitslagCreateType;
use App\Form\UitslagType;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

#[Route(path: 'admin/uitslag')]
class UitslagController extends AbstractController
{
    public function __construct(
        private readonly ManagerRegistry $doctrine,
        private readonly PaginatorInterface $paginator,
        private readonly Environment $twig,
    ) {
    }

    #[Route(path: '/', name: 'admin_uitslag')]
    public function indexAction(Request $request): Response
    {
        $em = $this->doctrine->getManager();

        $query = $em->createQuery('SELECT w FROM App\Entity\Uitslag w ORDER BY w.id DESC');

        $pagination = $this->paginator->paginate(
            $query, $request->query->get('page', 1)/* page number */, 20/* limit per page */
        );
        $seizoen = $em->getRepository(Seizoen::class)->getCurrent();
        return $this->render('Admin/Uitslag/index.html.twig', ['pagination' => $pagination, 'seizoen' => $seizoen]);
    }

    #[Route(path: '/{uitslag}/edit', name: 'admin_uitslag_edit')]
    public function editAction(Request $request, Uitslag $uitslag): Response
    {
        $em = $this->doctrine->getManager();
        $seizoen = $uitslag->getWedstrijd()->getSeizoen();
        $form = $this->createForm(UitslagType::class, $uitslag, ['seizoen' => $seizoen]);
        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em->flush();
                return $this->redirect($this->generateUrl('admin_uitslag_edit', ['uitslag' => $uitslag->getId()]));
            }
        }

        return $this->render('Admin/Uitslag/edit.html.twig', ['form' => $form->createView(), 'entity' => $uitslag]);
    }

    #[Route(path: '/new', name: 'admin_uitslag_new')]
    public function newAction(Request $request): Response
    {
        $uitslag = new Uitslag();
        $em = $this->doctrine->getManager();
        $seizoen = $em->getRepository(Seizoen::class)->getCurrent();
        $form = $this->createForm(UitslagType::class, $uitslag, ['seizoen' => $seizoen]);
        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em->persist($uitslag);
                $em->flush();
                return $this->redirect($this->generateUrl('admin_uitslag'));
            }
        }
        return $this->render('Admin/Uitslag/new.html.twig', ['form' => $form->createView()]);
    }

    #[Route(path: '/create', name: 'admin_uitslag_create')]
    public function createAction(Request $request): Response
    {
        $em = $this->doctrine->getManager();
        $options = [];
        $options['request'] = $request;
        $options['seizoen'] = $em->getRepository(Seizoen::class)->getCurrent();
        $options['default_date'] = new \DateTime();
        $form = $this->createForm(UitslagCreateType::class, null, $options);
        if ($request->isXmlHttpRequest()) {
            $form->handleRequest($request);
            // Render the whole template including any layouts etc
            $body = $this->twig->render('admin/uitslag/_ajaxTemplate.html.twig', ['form' => $form->createView()]);
            return $this->render('Admin/Uitslag/create.html.twig');
        }
        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            /** @var Wedstrijd $wedstrijd */
            $wedstrijd = $form->get('wedstrijd')->getData();
            $uitslagType = $form->get('wedstrijd')->get('uitslagtype')->getData();
            $wedstrijd->setGeneralClassification($uitslagType->getIsGeneralClassification());
            $wedstrijd->setFullyProcessed(true);
            $url = $form->get('url')->getData() ? $form->get('url')->getData() : $form->get('url_manual')->getData();
            // we use the last part of the URL as identifier
            $parts = explode('/', $url);
            $wedstrijd->setExternalIdentifier(end($parts));
            $uitslagen = $form->get('uitslag')->getData();
            $em->persist($wedstrijd);
            foreach ($uitslagen as $uitslag) {
                $em->persist($uitslag);
            }
            $em->flush();
            return $this->redirect($this->generateUrl('admin_uitslag_create'));
        }
        return $this->render('Admin/Uitslag/create.html.twig', ['form' => $form->createView()]);
    }
}
