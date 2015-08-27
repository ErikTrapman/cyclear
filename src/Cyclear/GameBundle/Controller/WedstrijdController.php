<?php

/*
 * This file is part of the Cyclear-game package.
 *
 * (c) Erik Trapman <veggatron@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cyclear\GameBundle\Controller;

use Cyclear\GameBundle\Entity\Seizoen;
use Cyclear\GameBundle\Entity\Wedstrijd;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/{seizoen}/wedstrijd")
 */
class WedstrijdController extends Controller
{

    /**
     * @Route("/latest", name="wedstrijd_latest")
     * @ParamConverter("seizoen", options={"mapping": {"seizoen": "slug"}})
     * @Template()
     */
    public function latestAction(Request $request, Seizoen $seizoen)
    {
        $em = $this->getDoctrine()->getManager();
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
