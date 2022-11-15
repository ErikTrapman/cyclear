<?php declare(strict_types=1);

namespace App\Controller;

use App\Entity\Seizoen;
use App\Entity\Spelregels;
use Doctrine\Persistence\ManagerRegistry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/{seizoen}/spelregels")
 */
class SpelregelsController extends AbstractController
{
    public function __construct(
        private readonly ManagerRegistry $doctrine
    ) {
    }

    /**
     * @Route ("/", name="spelregels_index")
     * @ParamConverter ("seizoen", options={"mapping": {"seizoen": "slug"}})
     * @Template ()
     */
    public function indexAction(Request $request, Seizoen $seizoen): array
    {
        $spelregels = $this->doctrine->getRepository(Spelregels::class)->createQueryBuilder('s')
            ->where('s.seizoen = :seizoen')->orderBy('s.id', 'DESC')->setMaxResults(1)
            ->setParameter('seizoen', $seizoen)
            ->getQuery()->getResult()[0] ?? null;

        return ['spelregels' => $spelregels, 'seizoen' => $seizoen];
    }
}
