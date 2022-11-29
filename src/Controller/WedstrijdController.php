<?php declare(strict_types=1);

namespace App\Controller;

use App\Entity\Seizoen;
use App\Entity\Wedstrijd;
use App\Repository\UitslagRepository;
use App\Repository\WedstrijdRepository;
use Knp\Component\Pager\PaginatorInterface;
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
    public function __construct(
        private readonly WedstrijdRepository $wedstrijdRepository,
        private readonly PaginatorInterface $paginator,
    ) {
    }

    /**
     * @Route("/latest", name="wedstrijd_latest")
     * @ParamConverter("seizoen", options={"mapping": {"seizoen": "slug"}})
     * @Template()
     */
    public function latestAction(Request $request, Seizoen $seizoen): array
    {
        $uitslagenQb = $this->wedstrijdRepository->createQueryBuilder('w')
            ->where('w.seizoen = :seizoen')->setParameter('seizoen', $seizoen)
            ->orderBy('w.datum', 'DESC')
            ->setMaxResults(20);
        return ['wedstrijden' => $uitslagenQb->getQuery()->getResult(), 'seizoen' => $seizoen];
    }

    /**
     * @Route("/{wedstrijd}", name="wedstrijd_show")
     * @Template()
     */
    public function showAction(Request $request, Wedstrijd $wedstrijd): array
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

        return [
            'wedstrijd' => $wedstrijd,
            'uitslagen' => array_merge($refStages, [$wedstrijd]),
            'allstages' => $allStages,
        ];
    }

    /**
     * @Route("en", name="wedstrijd_list")
     * @ParamConverter("seizoen", options={"mapping": {"seizoen": "slug"}})
     * @Template()
     */
    public function indexAction(Request $request, Seizoen $seizoen): array
    {
        $qb = $this->wedstrijdRepository->createQueryBuilder('n')
            ->where('n.seizoen = :seizoen')->setParameter('seizoen', $seizoen)
            ->orderBy('n.id', 'DESC');

        $pagination = $this->paginator->paginate(
            $qb, $request->query->get('page', 1), 20
        );
        return ['pagination' => $pagination, 'seizoen' => $seizoen];
    }
}
