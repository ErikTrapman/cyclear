<?php declare(strict_types=1);

namespace App\Controller\User;

use App\Entity\Ploeg;
use App\Entity\Renner;
use App\Entity\Seizoen;
use App\EntityManager\TransferManager;
use App\EntityManager\UserManager;
use App\Form\Entity\UserTransfer;
use App\Form\TransferUserType;
use App\Repository\PloegRepository;
use App\Repository\RennerRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Transfer controller.
 */
class TransferController extends AbstractController
{
    public function __construct(
        private readonly ManagerRegistry $doctrine,
        private readonly RennerRepository $rennerRepository,
        private readonly PloegRepository $ploegRepository,
    ) {
    }

    #[Route(path: '/user/{seizoen}/transfer/ploeg/{id}/renner/{renner}', name: 'user_transfer')]
    public function indexAction(
        UserManager $userManager,
        TransferManager $transferManager,
        Request $request,
        Seizoen $seizoen,
        Ploeg $ploeg,
        #[MapEntity(mapping: ['renner' => 'slug'])] Renner $renner): \Symfony\Component\HttpFoundation\Response
    {
        if (!$userManager->isOwner($this->getUser(), $ploeg)) {
            throw new AccessDeniedHttpException('Dit is niet jouw ploeg');
        }
        $transferUser = new UserTransfer();
        $transferUser->setPloeg($ploeg);
        $transferUser->setSeizoen($seizoen);
        $transferUser->setDatum(new \DateTime());

        $options = [];
        $rennerPloeg = $this->rennerRepository->getPloeg($renner, $seizoen);
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
                    $transferManager->doUserTransfer($ploeg, $form->get('renner_uit')->getData(), $renner, $seizoen, $form->get('userComment')->getData());
                } else {
                    $transferManager->doUserTransfer($ploeg, $renner, $form->get('renner_in')->getData(), $seizoen, $form->get('userComment')->getData());
                }
                $this->doctrine->getManager()->flush();
                return $this->render('user/transfer/index.html.twig');
            }
        }
        $transferInfo = $transferManager->getTtlTransfersDoneByPloeg($ploeg);
        $ttlTransfersAtm = $transferManager->getTtlTransfersAtm($seizoen);
        return
            $this->render('user/transfer/index.html.twig', [
                'ploeg' => $ploeg,
                'renner' => $renner,
                'form' => $form->createView(),
                'seizoen' => $seizoen,
                'transferInfo' => [
                    'count' => $transferInfo,
                    'left' => $ttlTransfersAtm - $transferInfo,
                ],
            ]);
    }
}
