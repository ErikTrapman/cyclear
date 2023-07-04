<?php declare(strict_types=1);

namespace App\Controller;

use App\Entity\Seizoen;
use App\Entity\Spelregels;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class SpelregelsController extends AbstractController
{
    public function __construct(
        private readonly ManagerRegistry $doctrine
    ) {
    }

    #[Route(path: '/{seizoen}/spelregels', name: 'spelregels_index')]
    public function indexAction(Request $request, Seizoen $seizoen): \Symfony\Component\HttpFoundation\Response
    {
        $spelregels = $this->doctrine->getRepository(Spelregels::class)->createQueryBuilder('s')
            ->where('s.seizoen = :seizoen')->orderBy('s.id', 'DESC')->setMaxResults(1)
            ->setParameter('seizoen', $seizoen)
            ->getQuery()->getResult()[0] ?? null;

        return $this->render('spelregels/index.html.twig', ['spelregels' => $spelregels, 'seizoen' => $seizoen]);
    }
}
