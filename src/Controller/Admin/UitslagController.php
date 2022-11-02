<?php declare(strict_types=1);

namespace App\Controller\Admin;

use App\CQRanking\Parser\Crawler\CrawlerManager;
use App\Entity\Seizoen;
use App\Entity\Uitslag;
use App\Entity\Wedstrijd;
use App\EntityManager\RennerManager;
use App\EntityManager\UitslagManager;
use App\EntityManager\WedstrijdManager;
use App\Form\UitslagCreateType;
use App\Form\UitslagType;
use DateTime;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("admin/uitslag")
 */
class UitslagController extends AbstractController
{
    public static function getSubscribedServices()
    {
        return array_merge(['knp_paginator' => PaginatorInterface::class,
            'cyclear_game.manager.uitslag' => UitslagManager::class,
            'cyclear_game.manager.wedstrijd' => WedstrijdManager::class,
            'cyclear_game.manager.renner' => RennerManager::class,
            'eriktrapman_cqparser.crawler_manager' => CrawlerManager::class,
        ],
            parent::getSubscribedServices());
    }

    /**
     * @Route("/", name="admin_uitslag")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $query = $em->createQuery('SELECT w FROM App\Entity\Uitslag w ORDER BY w.id DESC');

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query, $request->query->get('page', 1)/* page number */, 20/* limit per page */
        );
        $seizoen = $em->getRepository(Seizoen::class)->getCurrent();
        return ['pagination' => $pagination, 'seizoen' => $seizoen];
    }

    /**
     * @Route("/{uitslag}/edit", name="admin_uitslag_edit")
     * @Template()
     */
    public function editAction(Request $request, Uitslag $uitslag)
    {
        $em = $this->getDoctrine()->getManager();
        $seizoen = $uitslag->getWedstrijd()->getSeizoen();
        $form = $this->createForm(UitslagType::class, $uitslag, ['seizoen' => $seizoen]);
        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em->flush();
                return $this->redirect($this->generateUrl('admin_uitslag_edit', ['uitslag' => $uitslag->getId()]));
            }
        }

        return ['form' => $form->createView(), 'entity' => $uitslag];
    }

    /**
     * @Route("/new", name="admin_uitslag_new")
     * @Template()
     */
    public function newAction(Request $request)
    {
        $uitslag = new Uitslag();
        $em = $this->getDoctrine()->getManager();
        $seizoen = $em->getRepository(Seizoen::class)->getCurrent();
        $em = $this->getDoctrine()->getManager();
        $form = $this->createForm(UitslagType::class, $uitslag, ['seizoen' => $seizoen]);
        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em->persist($uitslag);
                $em->flush();
                return $this->redirect($this->generateUrl('admin_uitslag'));
            }
        }
        return ['form' => $form->createView()];
    }

    /**
     * Displays a form to create a new Periode entity.
     *
     * @Route("/create", name="admin_uitslag_create")
     * @Template()
     */
    public function createAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $uitslagManager = $this->get('cyclear_game.manager.uitslag');
        $wedstrijdManager = $this->get('cyclear_game.manager.wedstrijd');
        $crawlerManager = $this->get('eriktrapman_cqparser.crawler_manager');
        $rennerManager = $this->get('cyclear_game.manager.renner');
        $options = [];
        $options['crawler_manager'] = $crawlerManager;
        $options['wedstrijd_manager'] = $wedstrijdManager;
        $options['uitslag_manager'] = $uitslagManager;
        $options['request'] = $request;
        $options['seizoen'] = $em->getRepository(Seizoen::class)->getCurrent();
        $options['renner_manager'] = $rennerManager;
        $options['default_date'] = new DateTime();
        $form = $this->createForm(UitslagCreateType::class, null, $options);
        if ($request->isXmlHttpRequest()) {
            $form->handleRequest($request);

            $twig = $this->get('twig');
            $templateFile = 'admin/uitslag/_ajaxTemplate.html.twig';
            $templateContent = $twig->loadTemplate($templateFile);

            // Render the whole template including any layouts etc
            $body = $templateContent->render(['form' => $form->createView()]);
            return new Response($body);
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
        return ['form' => $form->createView()];
    }
}
