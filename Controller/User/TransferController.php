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
 * @Route("/game/{seizoen}/user/transfer")
 */
class TransferController extends Controller
{

    /**
     * My team.
     *
     * @Route("/ploeg/{id}/renner/{renner_id}", name="user_transfer")
     * @Template("CyclearGameBundle:Transfer/User:index.html.twig")
     * @SecureParam(name="id", permissions="OWNER")
     */
    public function indexAction($seizoen, $id, $renner_id)
    {

        $em = $this->getDoctrine()->getEntityManager();
        $ploeg = $em->find("CyclearGameBundle:Ploeg", $id);
        if (null === $ploeg) {
            throw new \RuntimeException("Unknown ploeg");
        }
        $renner = $em->find("CyclearGameBundle:Renner", $renner_id);
        if (null === $renner) {
            throw new \RuntimeException("Unknown renner");
        }
        $transferUser = new \Cyclear\GameBundle\Form\Entity\UserTransfer();
        $transferUser->setPloeg($ploeg);

        $options = array();
        if ($renner->getPloeg() !== $ploeg) {
            $options['renner_in'] = $renner;
            //throw new \Symfony\Component\Security\Core\Exception\AccessDeniedException("Renner is niet in je ploeg");
        } else {
            $options['renner_uit'] = $renner;
        }
        $seizoen = $this->getDoctrine()->getRepository("CyclearGameBundle:Seizoen")->findBySlug($seizoen);

        $options['ploeg'] = $ploeg;
        $form = $this->createForm(new \Cyclear\GameBundle\Form\TransferUserType(), $transferUser, $options);
        if ($this->getRequest()->getMethod() == 'POST') {
            $form->bindRequest($this->getRequest());
            if ($form->isValid()) {
                $transferManager = $this->get('cyclear_game.manager.transfer');
                if ($renner->getPloeg() !== $ploeg) {
                    $transferManager->doUserTransfer($ploeg, $form->get('renner_uit')->getData(), $renner, $seizoen[0]);
                } else {
                    $transferManager->doUserTransfer($ploeg, $renner, $form->get('renner_in')->getData(), $seizoen[0]);
                }
                $em->flush();
                return new \Symfony\Component\HttpFoundation\RedirectResponse($this->generateUrl("user_ploeg", array("seizoen" => $seizoen[0]->getSlug(), "id" => $ploeg->getId())));
            }
        }

        return array('ploeg' => $ploeg, 'renner' => $renner, 'form' => $form->createView(), 'seizoen' => $seizoen[0]);
    }
}