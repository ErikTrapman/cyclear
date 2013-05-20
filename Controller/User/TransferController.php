<?php

namespace Cyclear\GameBundle\Controller\User;

use Cyclear\GameBundle\Entity\Ploeg;
use Cyclear\GameBundle\Entity\Renner;
use Cyclear\GameBundle\Entity\Transfer;
use Cyclear\GameBundle\Form\Entity\UserTransfer;
use Cyclear\GameBundle\Form\TransferUserType;
use JMS\SecurityExtraBundle\Annotation\SecureParam;
use RuntimeException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Transfer controller.
 *
 * @Route("/{seizoen}/user/transfer")
 */
class TransferController extends Controller
{

    /**
     * My team.
     *
     * @Route("/ploeg/{id}/renner/{renner}", name="user_transfer")
     * @Template("CyclearGameBundle:Transfer/User:index.html.twig")
     * @SecureParam(name="id", permissions="OWNER")
     */
    public function indexAction($seizoen, $id, $renner)
    {
        $usermanager = $this->get('cyclear_game.manager.user');
        $em = $this->getDoctrine()->getManager();
        $seizoen = $this->getDoctrine()->getRepository("CyclearGameBundle:Seizoen")->findBySlug($seizoen);
        $ploeg = $em->find("CyclearGameBundle:Ploeg", $id);
        if (null === $ploeg) {
            throw new RuntimeException("Unknown ploeg");
        }
        if(!$usermanager->isOwner($this->getUser(), $ploeg)){
            throw new AccessDeniedHttpException("Dit is niet jouw ploeg");
        }
        $renner = $this->getDoctrine()->getRepository("CyclearGameBundle:Renner")->findOneBySlug($renner);
        if (null === $renner) {
            throw new RuntimeException("Unknown renner");
        }
        $transferUser = new UserTransfer();
        $transferUser->setPloeg($ploeg);
        $transferUser->setSeizoen($seizoen[0]);

        $options = array();
        $rennerPloeg = $em->getRepository("CyclearGameBundle:Renner")->getPloeg($renner, $seizoen);
        if ($rennerPloeg !== $ploeg) {
            if( null !== $rennerPloeg ){
                throw new AccessDeniedException("Renner is niet in je ploeg");
            } else {
                $options['renner_in'] = $renner;
            }
        } else {
            $options['renner_uit'] = $renner;
        }
        $options['ploegRenners'] = $this->getDoctrine()->getRepository("CyclearGameBundle:Ploeg")->getRenners($ploeg, $seizoen[0]);
        $options['ploeg'] = $ploeg;
        $form = $this->createForm(new TransferUserType(), $transferUser, $options);
        if ($this->getRequest()->getMethod() == 'POST') {
            $form->bindRequest($this->getRequest());
            if ($form->isValid()) {
                $transferManager = $this->get('cyclear_game.manager.transfer');
                if ($rennerPloeg !== $ploeg) {
                    $transferManager->doUserTransfer($ploeg, $form->get('renner_uit')->getData(), $renner, $seizoen[0]);
                } else {
                    $transferManager->doUserTransfer($ploeg, $renner, $form->get('renner_in')->getData(), $seizoen[0]);
                }
                $em->flush();
                return new RedirectResponse($this->generateUrl("user_ploeg", array("seizoen" => $seizoen[0]->getSlug(), "id" => $ploeg->getId())));
            }
        }
        
        $transferTypes = array(Transfer::ADMINTRANSFER,Transfer::USERTRANSFER);
        $periode = $em->getRepository("CyclearGameBundle:Periode")->getCurrentPeriode();
        $transferInfo = $em->getRepository("CyclearGameBundle:Transfer")
            ->getTransferCountByType($ploeg, $periode->getStart(), $periode->getEind(),$transferTypes);

        return array('ploeg' => $ploeg, 'renner' => $renner, 'form' => $form->createView(), 'seizoen' => $seizoen[0],
            'transferInfo' => array('count' => $transferInfo,'left' => $periode->getTransfers() - $transferInfo)
            );
    }
}