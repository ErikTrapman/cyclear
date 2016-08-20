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

use Cyclear\GameBundle\DataView\BloodHoundRiderView;
use Cyclear\GameBundle\DataView\RiderSearchView;
use Cyclear\GameBundle\Entity\Renner;
use Cyclear\GameBundle\Entity\Seizoen;
use Cyclear\GameBundle\Entity\Transfer;
use Cyclear\GameBundle\EntityManager\RennerManager;
use Doctrine\ORM\AbstractQuery;
use JMS\Serializer\SerializationContext;
use PDO;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Renner controller.
 *
 */
class RennerController extends Controller
{

    /**
     * @Route("/{seizoen}/renners.{_format}", name="rider_index", options={"_format"="json|html","expose"=true}, defaults={"_format":"html"})
     * @Route("/api/v1/{seizoen}/riders.{_format}", name="api_season_rider_index", options={"_format"="json"}, defaults={"_format":"json"})
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

    /**
     * @Route("/renners/get.{_format}", name="get_riders", options={"_format"="json"}, defaults={"_format"="json"})
     */
    public function getAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $paginator = $this->get('knp_paginator');
        $qb = $em->getRepository("CyclearGameBundle:Renner")->createQueryBuilder('r')->orderBy('r.naam', 'ASC');
        $urlQuery = $request->query->get('query');
        if (strlen($urlQuery) > 0) {
            if (is_numeric($urlQuery)) {
                $qb->where('r.cqranking_id = :identifier');
                $qb->setParameter('identifier', (int)$urlQuery);
            } else {
                $qb->where($qb->expr()->orx($qb->expr()->like('r.naam', ":naam")));
                $qb->setParameter('naam', "%" . $urlQuery . "%");
            }
        }
        $entities = $paginator->paginate(
            $qb, $request->query->get('page') !== null ? $request->query->get('page') : 1, 999
        );
        $serializer = $this->get('jms_serializer');
        $ret = [];
        foreach ($entities->getItems() as $item) {
            $ret[] = (new BloodHoundRiderView())->serialize($item)->getData();
        }
        return new Response($serializer->serialize($ret, 'json', SerializationContext::create()->setGroups(array('small'))));
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
     * @ParamConverter("seizoen", options={"mapping": {"seizoen": "slug"}})
     * @Template()
     */
    public function puntenAction(Request $request, Seizoen $seizoen)
    {
        $filter = $this->createForm('renner_filter');
        $em = $this->getDoctrine()->getManager();
        $sql = "SELECT * FROM Renner r ORDER BY r.naam";
        $conn = $em->getConnection();
        $params = array();
        $config = $em->getConfiguration();
        $config->addFilter("naam", "Cyclear\GameBundle\Filter\RennerNaamFilter");
        if ($request->getMethod() == 'POST') {
            $filter->submit($request);
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
        $listWithPunten = $this->getDoctrine()->getRepository("CyclearGameBundle:Uitslag")->getPuntenWithRenners($seizoen, 20);
        $listWithPuntenNoPloeg = $this->getDoctrine()->getRepository("CyclearGameBundle:Uitslag")->getPuntenWithRennersNoPloeg($seizoen, 20);

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $entities, $this->get('request')->query->get('page', 1), 10);

        return array(
            'pagination' => $pagination,
            'filter' => $filter->createView(),
            'listWithPunten' => $listWithPunten,
            'seizoen' => $seizoen,
            'rennerRepo' => $this->getDoctrine()->getRepository("CyclearGameBundle:Renner"),
            'listWithPuntenNoPloeg' => $listWithPuntenNoPloeg
        );
    }

    /**
     * @Route("/{seizoen}/renner/{renner}", name="renner_show", options={"expose"=true})
     * @Template("CyclearGameBundle:Renner:show.html.twig")
     * @ParamConverter("renner", class="CyclearGameBundle:Renner", options={"mapping": {"renner": "slug"}});
     * @ParamConverter("seizoen", options={"mapping": {"seizoen": "slug"}})
     */
    public function showAction(Seizoen $seizoen, Renner $renner)
    {
        $transferrepo = $this->getDoctrine()->getRepository("CyclearGameBundle:Transfer");
        $transfers = $transferrepo->findByRenner($renner, $seizoen, array(Transfer::ADMINTRANSFER, Transfer::USERTRANSFER, Transfer::DRAFTTRANSFER));
        $uitslagen = $this->getDoctrine()->getRepository("CyclearGameBundle:Uitslag")->getPuntenForRenner($renner, $seizoen, true);
        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $uitslagen, $this->get('request')->query->get('page', 1), 20
        );

        $ploeg = $this->getDoctrine()->getRepository("CyclearGameBundle:Renner")->getPloeg($renner, $seizoen);

        $punten = $this->getDoctrine()->getRepository("CyclearGameBundle:Uitslag")->getTotalPuntenForRenner($renner, $seizoen);

        return array(
            'seizoen' => $seizoen,
            'renner' => $renner,
            'transfers' => $transfers,
            'uitslagen' => $pagination,
            'transferrepo' => $transferrepo,
            'ploeg' => $ploeg,
            'rennerPunten' => intval($punten));
    }

    /**
     * @Route("/{seizoen}/download", name="renner_download")
     * @ParamConverter("seizoen", options={"mapping": {"seizoen": "slug"}})
     */
    public function csvDownloadAction(Request $request, Seizoen $seizoen)
    {
        $q = sprintf('SELECT r.id, r.naam, (SELECT SUM(rennerPunten) FROM Uitslag u
            INNER JOIN Wedstrijd w ON u.wedstrijd_id = w.id WHERE u.renner_id = r.id AND w.seizoen_id = %d ) AS pts
            FROM Renner r HAVING pts > 0 ORDER BY pts DESC, r.naam', $seizoen->getId());

        $em = $this->get('doctrine.orm.default_entity_manager');
        $delimiter = ';';
        $response = new StreamedResponse(function () use ($em, $q, $delimiter) {
            $stmt = $em->getConnection()->executeQuery($q);
            $handle = fopen('php://output', 'r+');
            fputcsv($handle, ['id', 'name', 'points'], $delimiter);
            foreach ($stmt->fetchAll() as $row) {
                fputcsv($handle, $row, $delimiter);
            }
            fclose($handle);
        });

        $response->headers->set('Content-Type', 'application/force-download');
        $filename = 'riders-' . $seizoen->getSlug() . date('-dmYHis') . '_65001utf8';
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s.csv"', $filename));

        return $response;
    }
}