<?php declare(strict_types=1);

namespace App\Controller;

use App\Entity\Nieuws;
use App\Entity\Seizoen;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/{seizoen}/nieuws")
 */
class NieuwsController extends AbstractController
{
    public function __construct(
        private readonly ManagerRegistry $doctrine,
        private readonly PaginatorInterface $paginator
    ) {
    }

    /**
     * @Route ("", name="nieuws")
     * @ParamConverter ("seizoen", options={"mapping": {"seizoen": "slug"}})
     * @Template ()
     */
    public function indexAction(Request $request, Seizoen $seizoen): array
    {
        $qb = $this->doctrine->getRepository(Nieuws::class)->createQueryBuilder('n')
            ->where('n.seizoen = :seizoen')->setParameter('seizoen', $seizoen)
            ->orderBy('n.id', 'DESC');

        $pagination = $this->paginator->paginate(
            $qb, $request->query->get('page', 1), 20
        );
        return ['pagination' => $pagination, 'seizoen' => $seizoen];
    }
}
