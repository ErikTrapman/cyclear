<?php declare(strict_types=1);

namespace App\Controller\User;

use App\Entity\Ploeg;
use App\Entity\Renner;
use App\Entity\Seizoen;
use App\EntityManager\TransferManager;
use App\EntityManager\UserManager;
use App\Form\Entity\UserTransfer;
use App\Form\TransferUserType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Transfer controller.
 *
 * @Route("/user/{seizoen}/transfer")
 */
class TransferController extends AbstractController
{
    /**
     * My team.
     *
     * @Route("/ploeg/{id}/renner/{renner}", name="user_transfer")
     * @Template()
     * @ParamConverter("seizoen", options={"mapping": {"seizoen": "slug"}})
     * @ParamConverter("renner", class="App\Entity\Renner", options={"mapping": {"renner": "slug"}});
     */
    public function indexAction(UserManager $userManager, TransferManager $transferManager, Request $request, Seizoen $seizoen, Ploeg $ploeg, Renner $renner)
    {
        $em = $this->getDoctrine()->getManager();
        if (!$userManager->isOwner($this->getUser(), $ploeg)) {
            throw new AccessDeniedHttpException('Dit is niet jouw ploeg');
        }
        $transferUser = new UserTransfer();
        $transferUser->setPloeg($ploeg);
        $transferUser->setSeizoen($seizoen);
        $transferUser->setDatum(new \DateTime());

        $options = [];
        $rennerPloeg = $em->getRepository(Renner::class)->getPloeg($renner, $seizoen);
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
        $options['ploegRenners'] = $this->getDoctrine()->getRepository(Ploeg::class)->getRenners($ploeg);
        $options['ploeg'] = $ploeg;
        $form = $this->createForm(TransferUserType::class, $transferUser, $options);
        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                if ($rennerPloeg !== $ploeg) {
                    $transferManager->doUserTransfer($ploeg, $form->get('renner_uit')->getData(), $renner, $seizoen, $form->get('userComment')->getData());
                } else {
                    $transferManager->doUserTransfer($ploeg, $renner, $form->get('renner_in')->getData(), $seizoen, $form->get('userComment')->getData());
                }
                $em->flush();
                return new RedirectResponse($this->generateUrl('ploeg_show', ['seizoen' => $seizoen->getSlug(), 'id' => $ploeg->getId()]));
            }
        }
        $transferInfo = $transferManager->getTtlTransfersDoneByPloeg($ploeg);
        $ttlTransfersAtm = $transferManager->getTtlTransfersAtm($seizoen);
        return
            [
                'ploeg' => $ploeg,
                'renner' => $renner,
                'form' => $form->createView(),
                'seizoen' => $seizoen,
                'transferInfo' => [
                    'count' => $transferInfo,
                    'left' => $ttlTransfersAtm - $transferInfo],
            ];
    }
}
