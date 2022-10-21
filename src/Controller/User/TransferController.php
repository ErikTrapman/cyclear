<?php

/*
 * This file is part of the Cyclear-game package.
 *
 * (c) Erik Trapman <veggatron@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\User;

use App\Entity\Ploeg;
use App\Entity\Renner;
use App\Entity\Seizoen;
use App\Entity\Transfer;
use App\EntityManager\TransferManager;
use App\EntityManager\UserManager;
use App\Form\Entity\UserTransfer;
use App\Form\TransferUserType;
use JMS\SecurityExtraBundle\Annotation\SecureParam;
use RuntimeException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Transfer controller.
 *
 * @Route("/user/{seizoen}/transfer")
 */
class TransferController extends AbstractController
{
    public static function getSubscribedServices()
    {
        return array_merge([
            'cyclear_game.manager.user' => UserManager::class,
            'cyclear_game.manager.transfer' => TransferManager::class
        ], parent::getSubscribedServices());
    }


    /**
     * My team.
     *
     * @Route("/ploeg/{id}/renner/{renner}", name="user_transfer")
     * @Template("transfer/User:index.html.twig")
     * @ParamConverter("seizoen", options={"mapping": {"seizoen": "slug"}})
     * @ParamConverter("renner", class="App\Entity\Renner", options={"mapping": {"renner": "slug"}});
     * SecureParam(name="id", permissions="OWNER")
     */
    public function indexAction(Request $request, Seizoen $seizoen, Ploeg $id, Renner $renner)
    {
        $usermanager = $this->get('cyclear_game.manager.user');
        $em = $this->getDoctrine()->getManager();
        $ploeg = $id;
        if (null === $ploeg) {
            throw new RuntimeException("Unknown ploeg");
        }
        if (!$usermanager->isOwner($this->getUser(), $ploeg)) {
            throw new AccessDeniedHttpException("Dit is niet jouw ploeg");
        }
        $transferUser = new UserTransfer();
        $transferUser->setPloeg($ploeg);
        $transferUser->setSeizoen($seizoen);
        $transferUser->setDatum(new \DateTime());

        $options = array();
        $rennerPloeg = $em->getRepository(Renner::class)->getPloeg($renner, $seizoen);
        if ($rennerPloeg !== $ploeg) {
            if (null !== $rennerPloeg) {
                throw new AccessDeniedHttpException("Renner is niet in je ploeg");
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
        $transferManager = $this->get('cyclear_game.manager.transfer');
        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                if ($rennerPloeg !== $ploeg) {
                    $transferManager->doUserTransfer($ploeg, $form->get('renner_uit')->getData(), $renner, $seizoen, $form->get('userComment')->getData());
                } else {
                    $transferManager->doUserTransfer($ploeg, $renner, $form->get('renner_in')->getData(), $seizoen, $form->get('userComment')->getData());
                }
                $em->flush();
                return new RedirectResponse($this->generateUrl("ploeg_show", array("seizoen" => $seizoen->getSlug(), "id" => $ploeg->getId())));
            }
        }
        $transferInfo = $transferManager->getTtlTransfersDoneByPloeg($ploeg);
        $ttlTransfersAtm = $transferManager->getTtlTransfersAtm($seizoen);
        return
            array(
                'ploeg' => $ploeg,
                'renner' => $renner,
                'form' => $form->createView(),
                'seizoen' => $seizoen,
                'transferInfo' => array(
                    'count' => $transferInfo,
                    'left' => $ttlTransfersAtm - $transferInfo)
            );
    }
}