<?php declare(strict_types=1);

namespace App\Controller;

use App\Entity\Seizoen;
use App\Entity\Wedstrijd;
use App\Repository\UitslagRepository;
use App\Repository\WedstrijdRepository;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class WedstrijdController extends AbstractController
{
    public function __construct(
        private readonly WedstrijdRepository $wedstrijdRepository,
        private readonly PaginatorInterface $paginator,
    ) {
    }

//    #[Route(path: '/{seizoen/wedstrijd/latest', name: 'wedstrijd_latest')]
//    public function latestAction(Request $request, #[MapEntity(mapping: ['seizoen' => 'slug'])] Seizoen $seizoen): \Symfony\Component\HttpFoundation\Response
//    {
//        $uitslagenQb = $this->wedstrijdRepository->createQueryBuilder('w')
//            ->where('w.seizoen = :seizoen')->setParameter('seizoen', $seizoen)
//            ->orderBy('w.datum', 'DESC')
//            ->setMaxResults(20);
//        return $this->render('wedstrijd/latest.html.twig', ['wedstrijden' => $uitslagenQb->getQuery()->getResult(), 'seizoen' => $seizoen]);
//    }

    #[Route(path: '/{seizoen}/wedstrijd/{wedstrijd}', name: 'wedstrijd_show')]
    public function showAction(Request $request, Wedstrijd $wedstrijd): \Symfony\Component\HttpFoundation\Response
    {
        $refStages = $this->wedstrijdRepository->getRefStages($wedstrijd);
        $allStages = [];
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

        return $this->render('wedstrijd/show.html.twig', [
            'wedstrijd' => $wedstrijd,
            'uitslagen' => array_merge($refStages, [$wedstrijd]),
            'allstages' => $allStages,
        ]);
    }

    #[Route(path: '/{seizoen}/wedstrijden', name: 'wedstrijd_list')]
    public function indexAction(Request $request, Seizoen $seizoen): \Symfony\Component\HttpFoundation\Response
    {
        $qb = $this->wedstrijdRepository->createQueryBuilder('n')
            ->where('n.seizoen = :seizoen')->setParameter('seizoen', $seizoen)
            ->orderBy('n.id', 'DESC');

        $pagination = $this->paginator->paginate(
            $qb, $request->query->get('page', 1), 20
        );
        return $this->render('wedstrijd/index.html.twig', ['pagination' => $pagination, 'seizoen' => $seizoen]);
    }
}
