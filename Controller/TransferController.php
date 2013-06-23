<?php

namespace Cyclear\GameBundle\Controller;

use Cyclear\GameBundle\Entity\Transfer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Ploeg controller.
 *
 * @Route("/{seizoen}/transfer")
 */
class TransferController extends Controller
{

    /**
     * Finds and displays a Ploeg entity.
     *
     * @Route("/latest", name="transfer_latest")
     * @Template()
     */
    public function viewLatestAction(Request $request)
    {
        $seizoen = $this->getDoctrine()->getRepository("CyclearGameBundle:Seizoen")->findBySlug($request->get('seizoen'));
        $list = $this->getDoctrine()->getRepository("CyclearGameBundle:Transfer")
            ->getLatest($seizoen[0], array(Transfer::ADMINTRANSFER, Transfer::USERTRANSFER), 20);
        return array('list' => $list);
    }
}