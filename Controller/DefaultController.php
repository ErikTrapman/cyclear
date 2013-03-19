<?php

namespace Cyclear\GameBundle\Controller;

use Cyclear\GameBundle\Entity\Transfer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;

/**
 *
 * @Route("/{seizoen}")
 */
class DefaultController extends Controller
{

    /**
     * @Route("/", name="game")
     * @Template()
     * @Cache(maxage="86400")
     */
    public function indexAction(Request $request)
    {
        $seizoen = $request->attributes->get('seizoen-object');
        $periode = $this->getDoctrine()->getRepository("CyclearGameBundle:Periode")->getCurrentPeriode();

        $nieuws = $this->getDoctrine()->getRepository("CyclearGameBundle:Nieuws")->findBy(array('seizoen' => $seizoen), array('id' => 'DESC'), 1);
        if (!array_key_exists(0, $nieuws)) {
            $nieuws = null;
        } else {
            $nieuws = $nieuws[0];
        }
        $doctrine = $this->getDoctrine();
        $stand = $doctrine->getRepository("CyclearGameBundle:Uitslag")->getPuntenByPloeg($seizoen);
        // TODO: dit naar repository-class
        $wedstrijden = $doctrine->getRepository("CyclearGameBundle:Wedstrijd")->createQueryBuilder('w')
            ->where('w.seizoen = :seizoen')->setParameter('seizoen', $seizoen)
            ->orderBy('w.datum', 'DESC')
            ->setMaxResults(20)->getQuery()->getResult();
        ;
        $periodestand = $doctrine->getRepository("CyclearGameBundle:Uitslag")->getPuntenByPloegForPeriode($periode, $seizoen);
        $zeges = $doctrine->getRepository("CyclearGameBundle:Uitslag")->getCountForPosition($seizoen, 1);
        $draft = $doctrine->getRepository("CyclearGameBundle:Uitslag")->getPuntenByPloegForDraftTransfers($seizoen);
        $transferstand = $doctrine->getRepository("CyclearGameBundle:Uitslag")->getPuntenByPloegForUserTransfers($seizoen);
        $transfers = $this->getDoctrine()->getRepository("CyclearGameBundle:Transfer")
            ->getLatestWithInversion($seizoen, array(Transfer::ADMINTRANSFER, Transfer::USERTRANSFER), 20);
        $transferRepo = $this->getDoctrine()->getRepository("CyclearGameBundle:Transfer");
        return array(
            'periode' => $periode, 
            'seizoen' => $seizoen, 
            'nieuws' => $nieuws,
            'stand' => $stand,
            'wedstrijden' => $wedstrijden,
            'periodestand' => $periodestand,
            'zegestand' => $zeges,
            'drafts' => $draft,
            'transferstand' => $transferstand,
            'transfers' => $transfers,
            'transferRepo' => $transferRepo
            );
    }
}