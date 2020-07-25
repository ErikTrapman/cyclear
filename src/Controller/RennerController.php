<?php

/*
 * This file is part of the Cyclear-game package.
 *
 * (c) Erik Trapman <veggatron@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller;

use App\DataView\BloodHoundRiderView;
use App\DataView\RiderSearchView;
use App\Entity\Renner;
use App\Entity\Seizoen;
use App\Entity\Transfer;
use App\Entity\Uitslag;
use App\EntityManager\RennerManager;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\QueryBuilder;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Knp\Component\Pager\Paginator;
use Knp\Component\Pager\PaginatorInterface;
use PDO;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Renner controller.
 *
 */
class RennerController extends AbstractController
{
    /**
     * @var PaginatorInterface
     */
    private PaginatorInterface $knpPaginator;
    /**
     * @var SerializerInterface
     */
    private SerializerInterface $serializer;

    public static function getSubscribedServices()
    {
        return array_merge(['knp_paginator' => PaginatorInterface::class, 'jms_serializer' => SerializerInterface::class],
            parent::getSubscribedServices());
    }

    public function __construct(PaginatorInterface $knpPaginator, SerializerInterface $serializer)
    {
        $this->knpPaginator = $knpPaginator;
        $this->serializer = $serializer;
    }

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
        $renners = $em->getRepository(Renner::class)->getRennersWithPunten($seizoen, $exclude);
        $paginator = $this->get('knp_paginator');

        $this->appendQuery($renners, $this->assertArray($request->query->get('filter'), "/\s+/"), array('r.naam'));

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
        $paginator = $this->knpPaginator;
        $qb = $em->getRepository(Renner::class)->createQueryBuilder('r')->orderBy('r.naam', 'ASC');
        $this->appendQuery($qb, $this->assertArray($request->query->get('query'), "/\s+/"), array('r.cqranking_id', 'r.naam', 'r.slug'));
        $entities = $paginator->paginate(
            $qb, $request->query->get('page') !== null ? $request->query->get('page') : 1, 20
        );
        $serializer = $this->serializer;
        $ret = [];
        foreach ($entities->getItems() as $item) {
            $ret[] = (new BloodHoundRiderView())->serialize($item)->getData();
        }
        return new Response($serializer->serialize($ret, 'json', SerializationContext::create()->setGroups(array('small'))));
    }

    /**
     * @Route("/{seizoen}/renner/{renner}", name="renner_show", options={"expose"=true})
     * @Template("renner/show.html.twig")
     * @ParamConverter("renner", class="App\Entity\Renner", options={"mapping": {"renner": "slug"}});
     * @ParamConverter("seizoen", options={"mapping": {"seizoen": "slug"}})
     */
    public function showAction(Request $request, Seizoen $seizoen, Renner $renner)
    {
        $doctrine = $this->getDoctrine();
        $transferrepo = $doctrine->getRepository(Transfer::class);
        $uitslagRepo = $doctrine->getRepository(Uitslag::class);
        $seizoenRepo = $doctrine->getRepository(Seizoen::class);

        $transfers = $transferrepo->findByRenner($renner, $seizoen, array(Transfer::ADMINTRANSFER, Transfer::USERTRANSFER, Transfer::DRAFTTRANSFER));
        $uitslagen = $uitslagRepo->getPuntenForRenner($renner, $seizoen, true);
        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $uitslagen, $request->query->get('page', 1), 20
        );

        $ploeg = $doctrine->getRepository(Renner::class)->getPloeg($renner, $seizoen);

        $punten = $uitslagRepo->getTotalPuntenForRenner($renner, $seizoen);
        // create archive links
        $puntenPerSeizoen = [];
        foreach ($seizoenRepo->findBy([], ['id' => 'ASC']) as $archivedSeizoen) {
            if ($archivedSeizoen === $seizoen) {
                continue;
            }
            $puntenPerSeizoen[] = [
                'seizoen' => $archivedSeizoen,
                'punten' => $uitslagRepo->getTotalPuntenForRenner($renner, $archivedSeizoen)
            ];
        }

        return array(
            'seizoen' => $seizoen,
            'renner' => $renner,
            'transfers' => $transfers,
            'uitslagen' => $pagination,
            'transferrepo' => $transferrepo,
            'ploeg' => $ploeg,
            'rennerPunten' => intval($punten),
            'puntenPerSeizoen' => $puntenPerSeizoen
        );
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

        $em = $this->get('doctrine');
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

    /**
     * @param $value
     * @param $separator
     * @return array|false|string[]
     */
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
     * Copied from https://github.com/SamsonIT/AutocompleteBundle/blob/master/Query/ResultsFetcher.php
     *
     * @param QueryBuilder $qb
     * @param array $searchWords
     * @param array $searchFields
     */
    private function appendQuery(QueryBuilder $qb, array $searchWords, array $searchFields)
    {
        foreach ($searchWords as $key => $searchWord) {
            $expressions = array();
            foreach ($searchFields as $key2 => $field) {
                $expressions[] = $qb->expr()->like($qb->expr()->lower($field), ':query' . $key . $key2);
                $qb->setParameter('query' . $key . $key2, '%' . strtolower($searchWord) . '%');
            }
            $qb->andWhere("(" . call_user_func_array(array($qb->expr(), 'orx'), $expressions) . ")");
        }
    }
}