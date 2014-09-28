<?php

/*
 * This file is part of the Cyclear-game package.
 *
 * (c) Erik Trapman <veggatron@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cyclear\GameBundle\Controller;

use Cyclear\GameBundle\DataView\RiderSearchView;
use Cyclear\GameBundle\Entity\Renner;
use Cyclear\GameBundle\Entity\Seizoen;
use Cyclear\GameBundle\Entity\Transfer;
use Cyclear\GameBundle\EntityManager\RennerManager;
use Doctrine\ORM\AbstractQuery;
use PDO;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Renner controller.
 *
 */
class RennerController extends Controller
{

    /**
     * @Route("/{seizoen}/renners.{_format}", name="rider_index", options={"_format"="json|html","expose"=true}, defaults={"_format":"html"})
     * @ParamConverter("seizoen", options={"mapping": {"seizoen": "slug"}})
     * @Template
     */
    public function indexAction(Request $request, Seizoen $seizoen)
    {
        $em = $this->getDoctrine()->getManager();
        $exclude = $request->query->get('excludeWithTeam') === 'true' ? true : false;
        $renners = $em->getRepository("CyclearGameBundle:Renner")->getRennersWithPunten($seizoen, $exclude);
        $paginator = $this->get('knp_paginator');

        $this->get('samson.autocomplete.results_fetcher')->getResultsByArray($this->assertArray($request->query->get('filter'), "/\s+/"), $request->query->get('page', 1), $renners, array('r.naam'));
        $pagination = $paginator->paginate($renners, $request->query->get('page', 1), 20);

        $ret = array();
        foreach ($pagination as $r) {
            $ret [] = (new RiderSearchView())->serialize($r)->getData();
        }
        $pagination->setItems($ret);
        $serializer = $this->get('jms_serializer');
        $entities = $serializer->serialize($pagination, 'json');

        if ('json' === $request->getRequestFormat()) {
            return new Response($entities);
        }

        return array('seizoen' => $seizoen);
    }

    private function assertArray($value, $separator)
    {
        if (is_array($value)) {
            return $value;
        }

        if ($separator[0] == '/') {
            return preg_split($separator, $value);
        } else {
            return explode($separator, $value);
        }
    }


    /**
     * @Route("/renner/search", name="renner_search", defaults={"_format"="json"})
     */
    public function searchAction(Request $request)
    {
        if (false !== strpos($request->headers->get('Accept'), 'twitter.typeahead')) {
            $query = $request->query->get('query');
            $em = $this->getDoctrine()->getManager();
            $qb = $em->getRepository("CyclearGameBundle:Renner")->createQueryBuilder('r');
            $qb->where('r.naam LIKE :naam')->setParameter('naam', "%" . $query . "%")->orderBy('r.naam');
            $ret = array();
            $ret['options'] = array();
            $rennerManager = new RennerManager();
            $renners = $qb->getQuery()->getResult(AbstractQuery::HYDRATE_ARRAY);
            foreach ($renners as $renner) {
                $ret['options'][] = $rennerManager->getRennerSelectorTypeString($renner['cqranking_id'], $renner['naam']);
            }
            $ret['ttl'] = count($renners);
            $response = new JsonResponse($ret);
            return $response;
        }
    }

    /**
     * @Route("/{seizoen}/renner/punten", name="renner_punten")
     * @Template()
     */
    public function puntenAction(Request $request)
    {
        $seizoen = $this->getDoctrine()->getRepository("CyclearGameBundle:Seizoen")->findBySlug($request->get('seizoen'));

        $filter = $this->createForm('renner_filter');
        $em = $this->getDoctrine()->getManager();
        $sql = "SELECT * FROM Renner r ORDER BY r.naam";
        $conn = $em->getConnection();
        $params = array();
        $config = $em->getConfiguration();
        $config->addFilter("naam", "Cyclear\GameBundle\Filter\RennerNaamFilter");
        if ($this->getRequest()->getMethod() == 'POST') {
            $filter->submit($this->getRequest());
            if ($filter->isValid()) {
                if ($filter->get('naam')->getData()) {
                    $em->getFilters()->enable("naam")->setParameter("naam", $filter->get('naam')->getData());
                    $sql = "SELECT * FROM Renner r WHERE r.naam LIKE :naam ORDER BY r.naam";
                    $params = array(":naam" => "%" . $filter->get('naam')->getData() . "%");
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
            $entities, $this->get('request')->query->get('page', 1) /* page number */, 10 /* limit per page */
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
     * @Route("/{seizoen}/renner/{renner}", name="renner_show", options={"expose"=true})
     * @Template("CyclearGameBundle:Renner:show.html.twig")
     * @ParamConverter("renner", class="CyclearGameBundle:Renner", options={"mapping": {"renner": "slug"}});
     */
    public function showAction($seizoen, $renner)
    {
        $seizoen = $this->getDoctrine()->getRepository("CyclearGameBundle:Seizoen")->findBySlug($seizoen);
        $transferrepo = $this->getDoctrine()->getRepository("CyclearGameBundle:Transfer");

        //$transfers = $this->getDoctrine()->getRepository("CyclearGameBundle:Transfer")->getLatest(
        //    $seizoen[0], array(Transfer::ADMINTRANSFER, Transfer::USERTRANSFER), 999, null, $renner);

        $transfers = $transferrepo->findByRenner($renner, $seizoen, array(Transfer::ADMINTRANSFER, Transfer::USERTRANSFER, Transfer::DRAFTTRANSFER));

        $uitslagen = $this->getDoctrine()->getRepository("CyclearGameBundle:Uitslag")->getPuntenForRenner($renner, $seizoen[0], true);

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $uitslagen, $this->get('request')->query->get('page', 1) /* page number */, 20 /* limit per page */
        );

        $ploeg = $this->getDoctrine()->getRepository("CyclearGameBundle:Renner")->getPloeg($renner, $seizoen[0]);

        $punten = $this->getDoctrine()->getRepository("CyclearGameBundle:Uitslag")->getTotalPuntenForRenner($renner, $seizoen[0]);

        return array(
            'seizoen' => $seizoen[0],
            'renner' => $renner,
            'transfers' => $transfers,
            'uitslagen' => $pagination,
            'transferrepo' => $transferrepo,
            'ploeg' => $ploeg,
            'rennerPunten' => $punten);
    }
}