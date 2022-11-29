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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Ploeg controller.
 *
 * @Route("/{seizoen}/ploeg")
 */
class PloegController extends AbstractController
{
    public function __construct(
        private readonly ManagerRegistry $doctrine,
        private readonly PaginatorInterface $paginator,
        private readonly PloegRepository $ploegRepository,
        private readonly UitslagRepository $uitslagRepository,
        private readonly TransferRepository $transferRepository,
        private readonly RennerRepository $rennerRepository,
    ) {
    }

    /**
     * @Route("/{id}/show", name="ploeg_show")
     * @ParamConverter("seizoen", options={"mapping": {"seizoen": "slug"}})
     * @Template()
     */
    public function showAction(Request $request, Seizoen $seizoen, Ploeg $id): array|RedirectResponse
    {
        $entity = $id;
        $renners = $this->ploegRepository->getRennersWithPunten($entity);

        $uitslagen = $this->paginator->paginate(
            $this->uitslagRepository->getUitslagenForPloegQb($entity, $seizoen)->getQuery()->getResult(), $request->query->get('page', 1), 20
        );
        $transfers = $this->paginator->paginate($this->transferRepository->getLatest(
            $seizoen, [Transfer::ADMINTRANSFER, Transfer::USERTRANSFER], 9999, $entity), $request->query->get('transferPage', 1), 20, ['pageParameterName' => 'transferPage']);
        $transferUitslagen = $this->paginator->paginate(
            $this->uitslagRepository->getUitslagenForPloegForNonDraftTransfersQb($entity, $seizoen)->getQuery()->getResult(), $request->query->get('transferResultsPage', 1), 20, ['pageParameterName' => 'transferResultsPage']
        );
        $lostDrafts = $this->paginator->paginate(
            $this->uitslagRepository->getUitslagenForPloegForLostDraftsQb($entity, $seizoen)->getQuery()->getResult(), $request->query->get('page', 1), 20
        );
        $zeges = $this->paginator->paginate(
            $this->uitslagRepository->getUitslagenForPloegByPositionQb($entity, 1, $seizoen)->getQuery()->getResult(), $request->query->get('zegeResultsPage', 1), 20, ['pageParameterName' => 'zegeResultsPage']
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

        return [
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
        ];
    }
}
