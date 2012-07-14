<?php

namespace Cyclear\GameBundle\Controller\User;

use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Cyclear\GameBundle\Entity\Ploeg,
    Cyclear\GameBundle\Entity\Renner;
use JMS\SecurityExtraBundle\Annotation\SecureParam;

/**
 * Transfer controller.
 *
 * @Route("/user/transfer")
 */
class TransferController extends Controller {

    /**
     * My team.
     *
     * @Route("/ploeg/{id}/renner/{renner_id}", name="user_transfer")
     * @Template("CyclearGameBundle:Transfer/User:index.html.twig")
     * @SecureParam(name="id", permissions="OWNER")
     */
    public function indexAction($id, $renner_id) {

        $em = $this->getDoctrine()->getEntityManager();
        $ploeg = $em->find("CyclearGameBundle:Ploeg", $id);
        if (null === $ploeg) {
            throw new \RuntimeException("Unknown ploeg");
        }
        $renner = $em->find("CyclearGameBundle:Renner", $renner_id);
        if (null === $renner) {
            throw new \RuntimeException("Unknown renner");
        }
        if($renner->getPloeg() !== $ploeg){
            throw new \Symfony\Component\Security\Core\Exception\AccessDeniedException("Renner is niet in je ploeg");
        }
        
        $transferUser = new \Cyclear\GameBundle\Form\Entity\UserTransfer();
        $transferUser->setPloeg($ploeg);
        $form = $this->createForm(new \Cyclear\GameBundle\Form\TransferUserType(), $transferUser);
        if ($this->getRequest()->getMethod() == 'POST') {
            $form->bindRequest($this->getRequest());
            if ($form->isValid()) {
                $transferManager = $this->get('cyclear_game.manager.transfer');
                $rennerIn = $form->get('renner_in')->getData();
                $transferManager->doUserTransfer($ploeg, $renner, $rennerIn);
                $em->flush();
                return new \Symfony\Component\HttpFoundation\RedirectResponse($this->generateUrl("user_ploeg", array("id" => $ploeg->getId())));
            }
        }

        return array('ploeg' => $ploeg, 'renner' => $renner, 'form' => $form->createView());
    }

}