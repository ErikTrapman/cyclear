<?php

namespace Cyclear\GameBundle\Controller;

use Cyclear\GameBundle\Entity\Ploeg;
use Cyclear\GameBundle\Entity\Renner;
use Cyclear\GameBundle\Entity\Transfer;
use Cyclear\GameBundle\EntityManager\RennerManager;
use Cyclear\GameBundle\Form\PloegType;
use Doctrine\ORM\AbstractQuery;
use PDO;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Ploeg controller.
 *
 */
class RennerController extends Controller
{

    /**
     * @Route("/renner/search", name="renner_search", defaults={"_format"="json"})
     */
    public function searchAction(Request $request)
    {
        if (false !== strpos($request->headers->get('Accept'), 'twitter.typeahead')) {
            $query = $request->query->get('query');
            $em = $this->getDoctrine()->getEntityManager();
            $qb = $em->getRepository("CyclearGameBundle:Renner")->createQueryBuilder('r');
            $qb->where('r.naam LIKE :naam')->setParameter('naam', "%".$query."%")->orderBy('r.naam');
            $ret = array();
            $ret['options'] = array();
            $rennerManager = new RennerManager();
            foreach($qb->getQuery()->getResult(AbstractQuery::HYDRATE_ARRAY) as $renner){
                $ret['options'][] = $rennerManager->getRennerSelectorTypeString( $renner['cqranking_id'], $renner['naam']);
            }
            $response = new JsonResponse($ret);
            return $response;
        }
    }

    /**
     * @Route("/{seizoen}/renner", name="renner_index")
     * @Template("CyclearGameBundle:Renner:index.html.twig")
     */
    public function indexAction($seizoen)
    {
//        $filter = $this->createForm('renner_filter');
//        $em = $this->getDoctrine();//->getEntityManager();
//
//        $config = $em->getConfiguration();
//        $config->addFilter("naam", "Cyclear\GameBundle\Filter\RennerNaamFilter");
//
//        if ($this->getRequest()->getMethod() == 'POST') {
//            $filter->bindRequest($this->getRequest());
//            if ($filter->isValid()) {
//                if ($filter->get('naam')->getData()) {
//                    $em->getFilters()->enable("naam")->setParameter("naam", $filter->get('naam')->getData());
//                }
//            }
//        }
//        $q='SELECT r FROM CyclearGameBundle:Rennerr r ORDER BY r.naam ASC';
//        $query = $this->getEntityManager()->createQuery($q);
//        /*
//        $query = $em->getRepository("CyclearGameBundle:Renner")
//            ->createQueryBuilder('r')
//            ->orderBy('RAND()')
//            ->getQuery();
//        $entities = $query->getResult();
//        */
//        $paginator = $this->get('knp_paginator');
//        $pagination = $paginator->paginate(
//            $query, $this->get('request')->query->get('page', 1)/* page number */, 10/* limit per page */
//        );
//
//
//        $seizoen = $this->getDoctrine()->getRepository("CyclearGameBundle:Seizoen")->findBySlug($seizoen);
//        return array('pagination' => $pagination, 'filter' => $filter->createView(), 'seizoen' => $seizoen[0]);
    }

    /**
     * @Route("/{seizoen}/renner/punten", name="renner_punten")
     * 
     * @Template()
     */
    public function puntenAction(Request $request)
    {
        $seizoen = $this->getDoctrine()->getRepository("CyclearGameBundle:Seizoen")->findBySlug($request->get('seizoen'));

        $filter = $this->createForm('renner_filter');
        $em = $this->getDoctrine()->getEntityManager();
        $sql = "SELECT * FROM Renner r ORDER BY r.naam";
        $conn = $em->getConnection();
        $params = array();
        $config = $em->getConfiguration();
        $config->addFilter("naam", "Cyclear\GameBundle\Filter\RennerNaamFilter");
        if ($this->getRequest()->getMethod() == 'POST') {
            $filter->bindRequest($this->getRequest());
            if ($filter->isValid()) {
                if ($filter->get('naam')->getData()) {
                    $em->getFilters()->enable("naam")->setParameter("naam", $filter->get('naam')->getData());
                    $sql = "SELECT * FROM Renner r WHERE r.naam LIKE :naam ORDER BY r.naam";
                    $params = array(":naam" => "%".$filter->get('naam')->getData()."%");
                }
            }
        }
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $entities = $stmt->fetchAll(PDO::FETCH_NAMED);
        if (array_key_exists('naam', $em->getFilters()->getEnabledFilters())) {
            $em->getFilters()->disable('naam');
        }
        $listWithPunten = $this->getDoctrine()->getRepository("CyclearGameBundle:Uitslag")->getPuntenWithRenners($seizoen[0], 20);
        $listWithPuntenNoPloeg = $this->getDoctrine()->getRepository("CyclearGameBundle:Uitslag")->getPuntenWithRennersNoPloeg($seizoen[0], 20);

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $entities, $this->get('request')->query->get('page', 1)/* page number */, 10/* limit per page */
        );

        return array(
            'pagination' => $pagination, 'filter' => $filter->createView(),
            'listWithPunten' => $listWithPunten,
            'seizoen' => $seizoen[0],
            'rennerRepo' => $this->getDoctrine()->getRepository("CyclearGameBundle:Renner"),
            'listWithPuntenNoPloeg' => $listWithPuntenNoPloeg
        );
    }

    /**
     * @Route("/{seizoen}/renner/{renner}", name="renner_show")
     * @Template("CyclearGameBundle:Renner:show.html.twig")
     */
    public function showAction($seizoen, Renner $renner)
    {
        $seizoen = $this->getDoctrine()->getRepository("CyclearGameBundle:Seizoen")->findBySlug($seizoen);

        $transfers = $this->getDoctrine()->getRepository("CyclearGameBundle:Transfer")->getLatestWithInversion(
            $seizoen[0], array(Transfer::ADMINTRANSFER, Transfer::USERTRANSFER), 999, null, $renner);
        //$transfers = $this->getDoctrine()->getRepository("CyclearGameBundle:Transfer")->findByRenner($renner, $seizoen[0]);
        $transferrepo = $this->getDoctrine()->getRepository("CyclearGameBundle:Transfer");

        $uitslagen = $this->getDoctrine()->getRepository("CyclearGameBundle:Uitslag")->getPuntenForRenner($renner, $seizoen[0]);

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $uitslagen, $this->get('request')->query->get('page', 1)/* page number */, 10/* limit per page */
        );

        $ploeg = $this->getDoctrine()->getRepository("CyclearGameBundle:Renner")->getPloeg($renner, $seizoen[0]);

        return array('seizoen' => $seizoen[0],
            'renner' => $renner,
            'transfers' => $transfers, 'uitslagen' => $pagination, 'transferrepo' => $transferrepo, 'ploeg' => $ploeg);
    }
}