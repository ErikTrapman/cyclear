<?php declare(strict_types=1);

namespace App\Controller;

use App\Entity\Seizoen;
use App\Entity\Wedstrijd;
use App\Repository\UitslagRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/{seizoen}/wedstrijd")
 */
class WedstrijdController extends AbstractController
{
    /**
     * @Route ("/latest", name="wedstrijd_latest")
     *
     * @ParamConverter ("seizoen", options={"mapping": {"seizoen": "slug"}})
     *
     * @Template ()
     *
     * @return (Seizoen|mixed)[]
     *
     * @psalm-return array{wedstrijden: mixed, seizoen: Seizoen}
     */
    public function latestAction(Request $request, Seizoen $seizoen): array
    {
        $em = $this->getDoctrine()->getManager();
        $uitslagenQb = $em->getRepository(Wedstrijd::class)->createQueryBuilder('w')
            ->where('w.seizoen = :seizoen')->setParameter('seizoen', $seizoen)
            ->orderBy('w.datum', 'DESC')
            ->setMaxResults(20);
        return ['wedstrijden' => $uitslagenQb->getQuery()->getResult(), 'seizoen' => $seizoen];
    }

    /**
     * @Route ("/{wedstrijd}", name="wedstrijd_show")
     *
     * @Template ()
     *
     * @return (Wedstrijd|array|mixed)[]
     *
     * @psalm-return array{wedstrijd: Wedstrijd, uitslagen: array, allstages: mixed}
     */
    public function showAction(Request $request, Wedstrijd $wedstrijd): array
    {
        $em = $this->getDoctrine()->getManager();
        $refStages = $em->getRepository(Wedstrijd::class)->getRefStages($wedstrijd, $wedstrijd);
        $allStages = [];
        /** @var Wedstrijd $refStage */
        foreach (array_merge($refStages, [$wedstrijd]) as $refStage) {
            foreach ($refStage->getUitslagenGrouped(true) as $team => $resultInfo) {
                if (array_key_exists($team, $allStages)) {
                    $allStages[$team]['total'] += $resultInfo['total'];
                    $allStages[$team]['hits'] += $resultInfo['hits'];
                    $allStages[$team]['renners'] = array_merge($allStages[$team]['renners'], $resultInfo['renners']);
                } else {
                    $allStages[$team] = $resultInfo;
                }
            }
        }
        UitslagRepository::puntenSort($allStages, 'hits', 'total');

        return [
            'wedstrijd' => $wedstrijd,
            'uitslagen' => array_merge($refStages, [$wedstrijd]),
            'allstages' => $allStages,
        ];
    }

    /**
     * @Route ("en", name="wedstrijd_list")
     *
     * @ParamConverter ("seizoen", options={"mapping": {"seizoen": "slug"}})
     *
     * @Template ()
     *
     * @return (Seizoen|mixed)[]
     *
     * @psalm-return array{pagination: mixed, seizoen: Seizoen}
     */
    public function indexAction(Request $request, Seizoen $seizoen): array
    {
        $em = $this->get('doctrine');

        $qb = $em->getRepository(Wedstrijd::class)->createQueryBuilder('n')
            ->where('n.seizoen = :seizoen')->setParameter('seizoen', $seizoen)
            ->orderBy('n.id', 'DESC');

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $qb, $request->query->get('page', 1), 20
        );
        return ['pagination' => $pagination, 'seizoen' => $seizoen];
    }
}
