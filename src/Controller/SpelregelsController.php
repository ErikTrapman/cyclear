<?php declare(strict_types=1);

namespace App\Controller;

use App\Entity\Seizoen;
use App\Entity\Spelregels;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/{seizoen}/spelregels')]
class SpelregelsController extends AbstractController
{
    public function __construct(
        private readonly ManagerRegistry $doctrine
    ) {
    }

    #[Route(path: '/', name: 'spelregels_index')]
    public function indexAction(Request $request, #[MapEntity(mapping: ['seizoen' => 'slug'])] Seizoen $seizoen): \Symfony\Component\HttpFoundation\Response
    {
        $spelregels = $this->doctrine->getRepository(Spelregels::class)->createQueryBuilder('s')
            ->where('s.seizoen = :seizoen')->orderBy('s.id', 'DESC')->setMaxResults(1)
            ->setParameter('seizoen', $seizoen)
            ->getQuery()->getResult()[0] ?? null;

        return $this->render('Spelregels/index.html.twig', ['spelregels' => $spelregels, 'seizoen' => $seizoen]);
    }
}
