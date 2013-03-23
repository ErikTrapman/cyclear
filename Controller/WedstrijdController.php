<?php

namespace Cyclear\GameBundle\Controller;

use Cyclear\GameBundle\Entity\Wedstrijd;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/{seizoen}/wedstrijd")
 */
class WedstrijdController extends Controller
{

    /**
     * @Route("/latest", name="wedstrijd_latest")
     * @Template()
     */
    public function latestAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $seizoen = $request->attributes->get('seizoen-object');
        $uitslagenQb = $em->getRepository("CyclearGameBundle:Wedstrijd")->createQueryBuilder('w')
            ->where('w.seizoen = :seizoen')->setParameter('seizoen', $seizoen)
            ->orderBy('w.datum', 'DESC')
            ->setMaxResults(20)
        ;
        return array('wedstrijden' => $uitslagenQb->getQuery()->getResult(), 'seizoen' => $seizoen);
    }

    /**
     * @Route("/{wedstrijd}", name="wedstrijd_show")
     * @Template()
     */
    public function showAction(Request $request, Wedstrijd $wedstrijd)
    {
        return array('wedstrijd' => $wedstrijd );
    }
}
