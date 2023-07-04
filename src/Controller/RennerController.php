<?php declare(strict_types=1);

namespace App\Controller;

use App\DataView\BloodHoundRiderView;
use App\DataView\RiderSearchView;
use App\Entity\Renner;
use App\Entity\Seizoen;
use App\Entity\Transfer;
use App\Repository\RennerRepository;
use App\Repository\SeizoenRepository;
use App\Repository\TransferRepository;
use App\Repository\UitslagRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
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
    public function __construct(
        private readonly PaginatorInterface $paginator,
        private readonly SerializerInterface $serializer,
        private readonly RennerRepository $rennerRepository,
        private readonly TransferRepository $transferRepository,
        private readonly UitslagRepository $uitslagRepository,
        private readonly SeizoenRepository $seizoenRepository,
        private readonly ManagerRegistry $doctrine,
    ) {
    }

    #[Route(path: '/{seizoen}/renners', name: 'rider_index', methods: ['GET', 'POST'])]
    public function indexAction(Request $request, Seizoen $seizoen): Response
    {
        $exclude = 'on' === $request->query->get('excludeWithTeam');
        $qb = $this->rennerRepository->getRennersWithPuntenQueryBuilder($seizoen, $exclude);

        $this->appendQuery($qb, $this->assertArray($request->query->get('filter'), "/\s+/"), ['r.naam']);

        $pagination = $this->paginator->paginate($qb, (int)$request->query->get('page', 1), 20);

        $ret = [];
        foreach ($pagination as $r) {
            $ret[] = (new RiderSearchView())->serialize($r);
        }
        $pagination->setItems($ret);

        return $this->render('renner/index.html.twig', ['seizoen' => $seizoen, 'pagination' => $pagination]);
    }

    #[Route(path: '/renners/get.{_format}', name: 'get_riders', options: ['_format' => 'json'], defaults: ['_format' => 'json'])]
    public function getAction(Request $request): Response
    {
        $qb = $this->rennerRepository->createQueryBuilder('r')->orderBy('r.naam', 'ASC');
        $this->appendQuery($qb, $this->assertArray($request->query->get('query'), "/\s+/"), ['r.cqranking_id', 'r.naam', 'r.slug']);
        $entities = $this->paginator->paginate(
            $qb, null !== $request->query->get('page') ? $request->query->get('page') : 1, 20
        );
        $ret = [];
        foreach ($entities->getItems() as $item) {
            $ret[] = (new BloodHoundRiderView())->serialize($item);
        }
        return new Response($this->serializer->serialize($ret, 'json', SerializationContext::create()->setGroups(['small'])));
    }

    #[Route(path: '/{seizoen}/renner/{renner}', name: 'renner_show', options: ['expose' => true])]
    public function showAction(Request $request, Seizoen $seizoen, #[MapEntity(mapping: ['renner' => 'slug'])] Renner $renner): Response
    {
        $transfers = $this->transferRepository->findByRenner($renner, $seizoen, [Transfer::ADMINTRANSFER, Transfer::USERTRANSFER, Transfer::DRAFTTRANSFER]);
        $uitslagen = $this->uitslagRepository->getPuntenForRenner($renner, $seizoen, true);
        $paginator = $this->paginator;
        $pagination = $paginator->paginate(
            $uitslagen, $request->query->get('page', 1), 999
        );

        $ploeg = $this->rennerRepository->getPloeg($renner, $seizoen);

        $punten = $this->uitslagRepository->getTotalPuntenForRenner($renner, $seizoen);
        // create archive links
        $puntenPerSeizoen = [];
        foreach ($this->seizoenRepository->findBy([], ['id' => 'ASC']) as $archivedSeizoen) {
            if ($archivedSeizoen === $seizoen) {
                continue;
            }
            $puntenPerSeizoen[] = [
                'seizoen' => $archivedSeizoen,
                'punten' => $this->uitslagRepository->getTotalPuntenForRenner($renner, $archivedSeizoen),
            ];
        }

        return $this->render('renner/show.html.twig', [
            'seizoen' => $seizoen,
            'renner' => $renner,
            'transfers' => $transfers,
            'uitslagen' => $pagination,
            'transferrepo' => $this->transferRepository,
            'ploeg' => $ploeg,
            'rennerPunten' => $punten,
            'puntenPerSeizoen' => $puntenPerSeizoen,
        ]);
    }

    #[Route(path: '/{seizoen}/download', name: 'renner_download')]
    public function csvDownloadAction(Request $request, Seizoen $seizoen): StreamedResponse
    {
        $q = sprintf('SELECT r.id, r.naam, (SELECT SUM(rennerPunten) FROM uitslag u
            INNER JOIN wedstrijd w ON u.wedstrijd_id = w.id WHERE u.renner_id = r.id AND w.seizoen_id = %d ) AS pts
            FROM renner r HAVING pts > 0 ORDER BY pts DESC, r.naam', $seizoen->getId());

        $em = $this->doctrine->getManager();
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
     * @psalm-param '/\s+/' $separator
     * @param mixed $value
     * @return array|false|string[]
     */
    private function assertArray($value, string $separator)
    {
        if (is_array($value)) {
            return $value;
        }
        if (null === $value) {
            return [];
        }

        if ('/' == $separator[0]) {
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
