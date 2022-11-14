<?php declare(strict_types=1);

namespace App\Controller;

use App\DataView\BloodHoundRiderView;
use App\DataView\RiderSearchView;
use App\Entity\Renner;
use App\Entity\Seizoen;
use App\Entity\Transfer;
use App\Entity\Uitslag;
use Doctrine\ORM\QueryBuilder;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Renner controller.
 */
class RennerController extends AbstractController
{
    private PaginatorInterface $knpPaginator;

    private SerializerInterface $serializer;

    public function __construct(PaginatorInterface $knpPaginator, SerializerInterface $serializer)
    {
        $this->knpPaginator = $knpPaginator;
        $this->serializer = $serializer;
    }

    public static function getSubscribedServices()
    {
        return array_merge(['knp_paginator' => PaginatorInterface::class, 'jms_serializer' => SerializerInterface::class],
            parent::getSubscribedServices());
    }

    /**
     * @Route ("/{seizoen}/renners.{_format}", name="rider_index", options={"_format"="json|html","expose"=true}, defaults={"_format":"html"})
     * @Route ("/api/v1/{seizoen}/riders.{_format}", name="api_season_rider_index", options={"_format"="json"}, defaults={"_format":"json"})
     *
     * @ParamConverter ("seizoen", options={"mapping": {"seizoen": "slug"}})
     *
     * @Template
     *
     * @return Response|Seizoen[]
     *
     * @psalm-return Response|array{seizoen: Seizoen}
     */
    public function indexAction(Request $request, Seizoen $seizoen): array|Response
    {
        $em = $this->getDoctrine()->getManager();
        $exclude = $request->query->get('excludeWithTeam') === 'true';
        $renners = $em->getRepository(Renner::class)->getRennersWithPunten($seizoen, $exclude);
        $paginator = $this->get('knp_paginator');

        $this->appendQuery($renners, $this->assertArray($request->query->get('filter'), "/\s+/"), ['r.naam']);

        $pagination = $paginator->paginate($renners, (int)$request->query->get('page', 1), 20);

        $ret = [];
        foreach ($pagination as $r) {
            $ret[] = (new RiderSearchView())->serialize($r);
        }
        $pagination->setItems($ret);
        $serializer = $this->get('jms_serializer');
        $entities = $serializer->serialize($pagination, 'json');

        if ('json' === $request->getRequestFormat()) {
            return new Response($entities);
        }

        return ['seizoen' => $seizoen];
    }

    /**
     * @Route ("/renners/get.{_format}", name="get_riders", options={"_format"="json"}, defaults={"_format"="json"})
     */
    public function getAction(Request $request): Response
    {
        $em = $this->getDoctrine()->getManager();
        $paginator = $this->knpPaginator;
        $qb = $em->getRepository(Renner::class)->createQueryBuilder('r')->orderBy('r.naam', 'ASC');
        $this->appendQuery($qb, $this->assertArray($request->query->get('query'), "/\s+/"), ['r.cqranking_id', 'r.naam', 'r.slug']);
        $entities = $paginator->paginate(
            $qb, $request->query->get('page') !== null ? $request->query->get('page') : 1, 20
        );
        $serializer = $this->serializer;
        $ret = [];
        foreach ($entities->getItems() as $item) {
            $ret[] = (new BloodHoundRiderView())->serialize($item);
        }
        return new Response($serializer->serialize($ret, 'json', SerializationContext::create()->setGroups(['small'])));
    }

    /**
     * @Route ("/{seizoen}/renner/{renner}", name="renner_show", options={"expose"=true})
     *
     * @Template ()
     *
     * @ParamConverter ("renner", class="App\Entity\Renner", options={"mapping": {"renner": "slug"}});
     * @ParamConverter ("seizoen", options={"mapping": {"seizoen": "slug"}})
     *
     * @return ((Seizoen|mixed)[][]|Renner|Seizoen|\Doctrine\Persistence\ObjectRepository|int|mixed)[]
     *
     * @psalm-return array{seizoen: Seizoen, renner: Renner, transfers: mixed, uitslagen: mixed, transferrepo: \Doctrine\Persistence\ObjectRepository<Transfer>, ploeg: mixed, rennerPunten: int, puntenPerSeizoen: list<array{seizoen: Seizoen, punten: mixed}>}
     */
    public function showAction(Request $request, Seizoen $seizoen, Renner $renner): array
    {
        $doctrine = $this->getDoctrine();
        $transferrepo = $doctrine->getRepository(Transfer::class);
        $uitslagRepo = $doctrine->getRepository(Uitslag::class);
        $seizoenRepo = $doctrine->getRepository(Seizoen::class);

        $transfers = $transferrepo->findByRenner($renner, $seizoen, [Transfer::ADMINTRANSFER, Transfer::USERTRANSFER, Transfer::DRAFTTRANSFER]);
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
                'punten' => $uitslagRepo->getTotalPuntenForRenner($renner, $archivedSeizoen),
            ];
        }

        return [
            'seizoen' => $seizoen,
            'renner' => $renner,
            'transfers' => $transfers,
            'uitslagen' => $pagination,
            'transferrepo' => $transferrepo,
            'ploeg' => $ploeg,
            'rennerPunten' => intval($punten),
            'puntenPerSeizoen' => $puntenPerSeizoen,
        ];
    }

    /**
     * @Route ("/{seizoen}/download", name="renner_download")
     *
     * @ParamConverter ("seizoen", options={"mapping": {"seizoen": "slug"}})
     */
    public function csvDownloadAction(Request $request, Seizoen $seizoen): StreamedResponse
    {
        $q = sprintf('SELECT r.id, r.naam, (SELECT SUM(rennerPunten) FROM uitslag u
            INNER JOIN wedstrijd w ON u.wedstrijd_id = w.id WHERE u.renner_id = r.id AND w.seizoen_id = %d ) AS pts
            FROM renner r HAVING pts > 0 ORDER BY pts DESC, r.naam', $seizoen->getId());

        $em = $this->get('doctrine');
        $delimiter = ';';
        $filename = 'riders-' . $seizoen->getSlug() . date('-dmYHis') . '_65001utf8';

        $response = new StreamedResponse(function () use ($em, $q, $delimiter) {
            $stmt = $em->getConnection()->executeQuery($q);
            $handle = fopen('php://output', 'r+');
            fputcsv($handle, ['id', 'name', 'points'], $delimiter);
            foreach ($stmt->fetchAllAssociative() as $row) {
                fputcsv($handle, $row, $delimiter);
            }
            fclose($handle);
        });
        $response->headers->set('Content-Type', 'text/csv');
        $disposition = HeaderUtils::makeDisposition(HeaderUtils::DISPOSITION_ATTACHMENT, $filename);
        $response->headers->set('Content-Disposition', $disposition);
        return $response;
    }

    /**
     * @return array|false|string[]
     *
     * @psalm-param '/\s+/' $separator
     */
    private function assertArray($value, string $separator)
    {
        if (is_array($value)) {
            return $value;
        }
        if (null === $value) {
            return [];
        }

        if ($separator[0] == '/') {
            return preg_split($separator, $value);
        }
        return explode($separator, $value);
    }

    /**
     * Copied from https://github.com/SamsonIT/AutocompleteBundle/blob/master/Query/ResultsFetcher.php
     */
    private function appendQuery(QueryBuilder $qb, array $searchWords, array $searchFields): void
    {
        foreach ($searchWords as $key => $searchWord) {
            $expressions = [];
            foreach ($searchFields as $key2 => $field) {
                $expressions[] = $qb->expr()->like($qb->expr()->lower($field), ':query' . $key . $key2);
                $qb->setParameter('query' . $key . $key2, '%' . strtolower($searchWord) . '%');
            }
            $qb->andWhere('(' . call_user_func_array([$qb->expr(), 'orx'], $expressions) . ')');
        }
    }
}
