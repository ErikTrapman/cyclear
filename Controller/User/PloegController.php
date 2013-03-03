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
 * @Route("/{seizoen}/user/ploeg")
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
        return $this->forward("CyclearGameBundle:Ploeg:show", array('seizoen'=>$seizoen,'id'=>$ploeg->getId()));
    }
}