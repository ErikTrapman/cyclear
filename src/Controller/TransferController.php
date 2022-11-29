<?php declare(strict_types=1);

namespace App\Controller;

use App\Entity\Seizoen;
use App\Entity\Transfer;
use App\Repository\TransferRepository;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/{seizoen}/transfer")
 */
class TransferController extends AbstractController
{
    public function __construct(
        private readonly TransferRepository $transferRepository,
        private readonly PaginatorInterface $paginator
    ) {
    }

    /**
     * @Route("s", name="transfer_list")
     * @ParamConverter("seizoen", options={"mapping": {"seizoen": "slug"}})
     * @Template()
     */
    public function indexAction(Request $request, Seizoen $seizoen): array
    {
        $qb = $this->transferRepository->createQueryBuilder('t')
            ->where('t.seizoen = :seizoen')
            ->andWhere('t.ploegNaar IS NOT NULL')
            ->andWhere('t.transferType > :draft')
            ->setParameter('seizoen', $seizoen)
            ->setParameter('draft', Transfer::DRAFTTRANSFER)
            ->orderBy('t.id', 'DESC');

        $pagination = $this->paginator->paginate($qb, (int)$request->query->get('page', 1), 20);

        return [
            'pagination' => $pagination,
            'seizoen' => $seizoen,
        ];
    }
}
