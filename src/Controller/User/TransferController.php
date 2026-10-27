<?php declare(strict_types=1);

namespace App\Controller\User;

use App\Dto\PaletteTransfer;
use App\Entity\Ploeg;
use App\Entity\Renner;
use App\EntityManager\TransferManager;
use App\Form\Entity\UserTransfer;
use App\Form\TransferUserType;
use App\Repository\PloegRepository;
use App\Repository\RennerRepository;
use App\Security\PloegVoter;
use App\Validator\Constraints\UserTransfer as UserTransferConstraint;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Transfer controller.
 */
class TransferController extends AbstractController
{
    public function __construct(
        private readonly ManagerRegistry $doctrine,
        private readonly RennerRepository $rennerRepository,
        private readonly PloegRepository $ploegRepository,
        private readonly TransferManager $transferManager,
        private readonly ValidatorInterface $validator,
    ) {
    }

    #[Route(path: '/user/transfer/ploeg/{id}/renner/{renner}', name: 'user_transfer')]
    public function indexAction(
        TransferManager $transferManager,
        Request $request,
        Ploeg $ploeg,
        #[MapEntity(mapping: ['renner' => 'slug'])] Renner $renner): Response|RedirectResponse
    {
        if (!$this->isGranted(PloegVoter::OWNER, $ploeg)) {
            throw new AccessDeniedHttpException('Dit is niet jouw ploeg');
        }
        $transferUser = new UserTransfer();
        $transferUser->setPloeg($ploeg);
        $transferUser->setSeizoen($ploeg->getSeizoen());
        $transferUser->setDatum(new \DateTime());

        $options = [];
        $rennerPloeg = $this->rennerRepository->getPloeg($renner, $ploeg->getSeizoen());
        if ($rennerPloeg !== $ploeg) {
            if (null !== $rennerPloeg) {
                throw new AccessDeniedHttpException('Renner is niet in je ploeg');
            } else {
                $options['renner_in'] = $renner;
                $transferUser->setRennerIn($renner);
            }
        } else {
            $options['renner_uit'] = $renner;
            $transferUser->setRennerUit($renner);
        }
        $options['ploegRenners'] = $this->ploegRepository->getRenners($ploeg);
        $options['ploeg'] = $ploeg;
        $form = $this->createForm(TransferUserType::class, $transferUser, $options);
        if ('POST' == $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                if ($rennerPloeg !== $ploeg) {
                    $transferManager->doUserTransfer($ploeg, $form->get('renner_uit')->getData(), $renner, $ploeg->getSeizoen(), $form->get('userComment')->getData());
                } else {
                    $transferManager->doUserTransfer($ploeg, $renner, $form->get('renner_in')->getData(), $ploeg->getSeizoen(), $form->get('userComment')->getData());
                }
                $this->doctrine->getManager()->flush();
                return new RedirectResponse($this->generateUrl('ploeg_show', ['seizoen' => $ploeg->getSeizoen()->getSlug(), 'id' => $ploeg->getId()]));
            }
        }
        $transferInfo = $transferManager->getTtlTransfersDoneByPloeg($ploeg);
        $ttlTransfersAtm = $transferManager->getTtlTransfersAtm($ploeg->getSeizoen());
        return
            $this->render('user/transfer/index.html.twig', [
                'ploeg' => $ploeg,
                'renner' => $renner,
                'form' => $form->createView(),
                'seizoen' => $ploeg->getSeizoen(),
                'transferInfo' => [
                    'count' => $transferInfo,
                    'left' => $ttlTransfersAtm - $transferInfo,
                ],
            ]);
    }

    /**
     * The riders of a ploeg for the "who goes out" step of the command palette, alphabetically.
     */
    #[Route(path: '/user/transfer/ploeg/{id}/palette', name: 'user_transfer_palette_team', methods: ['GET'])]
    public function paletteTeamAction(Ploeg $ploeg): JsonResponse
    {
        if (!$this->isGranted(PloegVoter::OWNER, $ploeg)) {
            throw new AccessDeniedHttpException('Dit is niet jouw ploeg');
        }
        $seizoen = $ploeg->getSeizoen();
        $rows = $this->ploegRepository->getRennersWithPunten($ploeg);
        usort($rows, static fn (array $a, array $b): int => strcasecmp($a[0]->getNaam(), $b[0]->getNaam()));

        $riders = [];
        foreach ($rows as $row) {
            $renner = $row[0];
            $riders[] = [
                'name' => $renner->getNaam(),
                'slug' => $renner->getSlug(),
                'country' => $renner->getCountry() ? $renner->getCountryIso() : null,
                'points' => $row['punten'],
                'since' => $renner->getLatestContract()?->getStart()?->format('Y-m-d'),
            ];
        }

        return $this->json([
            'riders' => $riders,
            'transfersLeft' => max(0, $this->transferManager->getTtlTransfersAtm($seizoen) - $this->transferManager->getTtlTransfersDoneByPloeg($ploeg)),
        ]);
    }

    /**
     * Performs a transfer confirmed in the command palette, with the same checks as the transfer form.
     */
    #[Route(path: '/user/transfer/ploeg/{id}/palette', name: 'user_transfer_palette', methods: ['POST'])]
    public function paletteTransferAction(Request $request, Ploeg $ploeg, #[MapRequestPayload] PaletteTransfer $payload): JsonResponse
    {
        if (!$this->isGranted(PloegVoter::OWNER, $ploeg)) {
            throw new AccessDeniedHttpException('Dit is niet jouw ploeg');
        }
        if (!$this->isCsrfTokenValid('palette-transfer', (string)$request->headers->get('X-CSRF-Token'))) {
            return $this->json(['errors' => ['Je sessie is verlopen, herlaad de pagina.']], Response::HTTP_FORBIDDEN);
        }
        $rennerIn = $this->rennerRepository->findOneBy(['slug' => $payload->rennerIn]);
        $rennerUit = $this->rennerRepository->findOneBy(['slug' => $payload->rennerUit]);
        if (null === $rennerIn || null === $rennerUit) {
            return $this->json(['errors' => ['Onbekende renner opgegeven.']], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        if (!in_array($rennerUit, $this->ploegRepository->getRenners($ploeg), true)) {
            return $this->json(['errors' => [$rennerUit->getNaam() . ' zit niet in je ploeg.']], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $seizoen = $ploeg->getSeizoen();
        $transferUser = new UserTransfer();
        $transferUser->setPloeg($ploeg);
        $transferUser->setSeizoen($seizoen);
        $transferUser->setDatum(new \DateTime());
        $transferUser->setRennerIn($rennerIn);
        $transferUser->setRennerUit($rennerUit);
        $violations = $this->validator->validate($transferUser, new UserTransferConstraint());
        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[] = $violation->getMessage();
            }
            return $this->json(['errors' => $errors], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $this->transferManager->doUserTransfer($ploeg, $rennerUit, $rennerIn, $seizoen, $payload->comment);
        $this->doctrine->getManager()->flush();

        return $this->json([
            'message' => $rennerIn->getNaam() . ' is binnengehaald, ' . $rennerUit->getNaam() . ' is vertrokken.',
            'url' => $this->generateUrl('ploeg_show', ['seizoen' => $seizoen->getSlug(), 'id' => $ploeg->getId()]),
        ]);
    }
}
