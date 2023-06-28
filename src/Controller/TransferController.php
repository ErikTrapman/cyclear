<?php declare(strict_types=1);

namespace App\Controller;

use App\Entity\Seizoen;
use App\Entity\Transfer;
use App\Repository\TransferRepository;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class TransferController extends AbstractController
{
    public function __construct(
        private readonly TransferRepository $transferRepository,
        private readonly PaginatorInterface $paginator
    ) {
    }

    #[Route(path: '/{seizoen}/transfer', name: 'transfer_list')]
    public function indexAction(Request $request, Seizoen $seizoen): \Symfony\Component\HttpFoundation\Response
    {
        $qb = $this->transferRepository->createQueryBuilder('t')
            ->where('t.seizoen = :seizoen')
            ->andWhere('t.ploegNaar IS NOT NULL')
            ->andWhere('t.transferType > :draft')
            ->setParameter('seizoen', $seizoen)
            ->setParameter('draft', Transfer::DRAFTTRANSFER)
            ->orderBy('t.id', 'DESC');

        $pagination = $this->paginator->paginate($qb, (int)$request->query->get('page', 1), 20);

        return $this->render('transfer/index.html.twig', [
            'pagination' => $pagination,
            'seizoen' => $seizoen,
        ]);
    }
}
