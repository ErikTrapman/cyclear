<?php declare(strict_types=1);

namespace App\Controller;

use App\Entity\Ploeg;
use App\Entity\Seizoen;
use App\Entity\Transfer;
use App\Repository\PloegRepository;
use App\Repository\RennerRepository;
use App\Repository\TransferRepository;
use App\Repository\UitslagRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PloegController extends AbstractController
{
    public function __construct(
        private readonly ManagerRegistry    $doctrine,
        private readonly PaginatorInterface $paginator,
        private readonly PloegRepository    $ploegRepository,
        private readonly UitslagRepository  $uitslagRepository,
        private readonly TransferRepository $transferRepository,
        private readonly RennerRepository   $rennerRepository,
    )
    {
    }

    #[Route(path: '/{seizoen}/ploeg/{id}/show', name: 'ploeg_show')]
    public function showAction(Request $request, Seizoen $seizoen, Ploeg $id): Response
    {
        $entity = $id;
        $renners = $this->ploegRepository->getRennersWithPunten($entity);

        $currentPage = (int)$request->query->get('page', 1);
        $uitslagen = $this->paginator->paginate(
            $this->uitslagRepository->getUitslagenForPloegQb($entity, $seizoen)->getQuery()->getResult(),
            (int)$request->query->get('resultsPage', 1), 99, ['pageParameterName' => 'resultsPage']
        );
        $transfers =$this->transferRepository->getLatest(
            $seizoen, [Transfer::ADMINTRANSFER, Transfer::USERTRANSFER], 9999, $entity);
        $transferUitslagen = $this->paginator->paginate(
            $this->uitslagRepository->getUitslagenForPloegForNonDraftTransfersQb($entity, $seizoen)->getQuery()->getResult(),
            (int)$request->query->get('transferResultsPage', 1), 999, ['pageParameterName' => 'transferResultsPage']
        );
        $lostDrafts = $this->paginator->paginate(
            $this->uitslagRepository->getUitslagenForPloegForLostDraftsQb($entity, $seizoen)->getQuery()->getResult(), $currentPage, 999
        );
        $zeges = $this->paginator->paginate(
            $this->uitslagRepository->getUitslagenForPloegByPositionQb($entity, 1, $seizoen)->getQuery()->getResult(),
            (int)$request->query->get('zegeResultsPage', 1), 999, ['pageParameterName' => 'zegeResultsPage']
        );

        $punten = $this->uitslagRepository->getPuntenByPloeg($seizoen, $entity);
        $draftRenners = $this->ploegRepository->getDraftRennersWithPunten($entity, false);

        $form = $this->createFormBuilder($entity)
            ->add('memo', null, ['attr' => ['placeholder' => '...', 'rows' => 16]])
            ->add('save', SubmitType::class)
            ->getForm();

        if ('POST' === $request->getMethod()) {
            if ($form->handleRequest($request)->isValid()) {
                $this->doctrine->getManager()->flush();
                return $this->redirect($this->generateUrl('ploeg_show', ['id' => $entity->getId(), 'seizoen' => $seizoen->getSlug()]));
            }
        }

        return $this->render('ploeg/show.html.twig', [
            'entity' => $entity,
            'renners' => $renners,
            'uitslagen' => $uitslagen,
            'seizoen' => $seizoen,
            'transfers' => $transfers,
            'rennerRepo' => $this->rennerRepository,
            'transferUitslagen' => $transferUitslagen,
            'lostDrafts' => $lostDrafts,
            'zeges' => $zeges,
            'punten' => $punten[0]['punten'],
            'draftRenners' => $draftRenners,
            'draftPunten' => array_sum(array_map(function ($el) {
                return $el['punten'];
            }, $draftRenners)),
            'form' => $form->createView(),
        ]);
    }
}
