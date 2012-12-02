<?php

namespace Cyclear\GameBundle\Controller\User;

use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Cyclear\GameBundle\Entity\Ploeg;
use Cyclear\GameBundle\Form\PloegType;
use JMS\SecurityExtraBundle\Annotation\SecureParam;

/**
 * Ploeg controller.
 *
 * @Route("/game/{seizoen}/user/ploeg")
 */
class PloegController extends Controller
{

    /**
     * My team.
     * 
     * TODO maak van index-action de edit-action.
     * 
     * @Route("/{id}", name="user_ploeg")
     * @Template("CyclearGameBundle:Ploeg/User:index.html.twig")
     * @SecureParam(name="ploeg", permissions="OWNER")
     */
    public function indexAction($seizoen, Ploeg $ploeg)
    {
        $em = $this->getDoctrine()->getEntityManager();
        if (null === $ploeg) {
            throw new \RuntimeException("Unknown ploeg");
        }
        $seizoen = $this->getDoctrine()->getRepository("CyclearGameBundle:Seizoen")->findBySlug($seizoen);
        $ploeglist = $this->getDoctrine()->getRepository("CyclearGameBundle:Ploeg")->getRennersWithPunten($ploeg);
        // TODO make this a service
        foreach($ploeglist as $index => $listentry){ 
            $renner = $em->getRepository("CyclearGameBundle:Renner")->find($listentry['rennerId']);
            $ploeglist[$index]['renner'] = $renner;
            $transfer = $em->getRepository("CyclearGameBundle:Transfer")->findLastByRenner($renner);
            $ploeglist[$index]['transferred'] = $transfer;
        }
        return array('ploeg' => $ploeg, 'ploeglist' => $ploeglist, 'seizoen' => $seizoen[0]);
    }
}