<?php
/*
 * This file is part of the Cyclear-game package.
 *
 * (c) Erik Trapman <veggatron@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller;

use App\Entity\Nieuws;
use App\Entity\Periode;
use App\Entity\Ploeg;
use App\Entity\Seizoen;
use App\Entity\Transfer;
use App\Entity\Uitslag;
use App\Entity\Wedstrijd;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 *
 * @Route("/{seizoen}")
 */
class DefaultController extends AbstractController
{

    /**
     * @Route("/", name="game")
     * @ParamConverter("seizoen", options={"mapping": {"seizoen": "slug"}})
     * @Template()
     */
    public function indexAction(Request $request, Seizoen $seizoen)
    {
        $doctrine = $this->getDoctrine();
        $periode = $this->getDoctrine()->getRepository(Periode::class)->getCurrentPeriode($seizoen);
        $nieuws = $this->getDoctrine()->getRepository(Nieuws::class)->findBy(array('seizoen' => $seizoen), array('id' => 'DESC'), 1);
        $stand = $doctrine->getRepository(Uitslag::class)->getPuntenByPloeg($seizoen);
        if (!array_key_exists(0, $nieuws)) {
            $nieuws = null;
        } else {
            $nieuws = $nieuws[0];
        }
        $shadowStandingsById = array();
        if (null !== $periode) {
            $refdate = $periode->getStart();
        } else {
            $refdate = new \DateTime;
        }
        foreach ($doctrine->getRepository(Uitslag::class)->getPuntenByPloeg($seizoen, null, $refdate) as $key => $value) {
            $value['position'] = $key + 1;
            $shadowStandingsById[$value[0]->getId()] = $value;
        }
        $wedstrijden = $doctrine->getRepository(Wedstrijd::class)->createQueryBuilder('w')
            ->where('w.seizoen = :seizoen')->setParameter('seizoen', $seizoen)
            ->orderBy('w.datum DESC, w.id', 'DESC')
            ->setMaxResults(20)->getQuery()->getResult();
        $periodestand = $periode ? $doctrine->getRepository(Uitslag::class)->getPuntenByPloegForPeriode($periode, $seizoen) : [];
        $gainedTransferpoints = [];
        if ($periode) {
            foreach ($doctrine->getRepository(Uitslag::class)
                         ->getPuntenByPloegForUserTransfersWithoutLoss($seizoen, $periode->getStart(), $periode->getEind()) as $teamResult) {
                $gainedTransferpoints[$teamResult['id']] = $teamResult['punten'];
            }
        }
        $lostDraftPoints = [];
        if ($periode) {
            foreach ($doctrine->getRepository(Uitslag::class)->getLostDraftPuntenByPloeg($seizoen, $periode->getStart(), $periode->getEind()) as $teamResult) {
                if ($teamResult instanceof Ploeg) {
                    $lostDraftPoints[$teamResult->getId()] = $teamResult->getPunten();
                } else {
                    $lostDraftPoints[$teamResult['id']] = $teamResult['punten'];
                }
            }
        }
        $transferSaldo = [];
        foreach ($gainedTransferpoints as $teamId => $gainedPoints) {
            $transferSaldo[$teamId] = $gainedPoints - $lostDraftPoints[$teamId];
        }
        $zeges = $doctrine->getRepository(Uitslag::class)->getCountForPosition($seizoen, 1);
        $zegesInPeriode = [];
        if ($periode) {
            foreach ($doctrine->getRepository(Uitslag::class)->getCountForPosition($seizoen, 1, $periode->getStart(), $periode->getEind()) as $teamResult) {
                $zegesInPeriode[$teamResult[0]->getId()] = $teamResult['freqByPos'];
            }
        }
        $draft = $doctrine->getRepository(Uitslag::class)->getPuntenByPloegForDraftTransfers($seizoen);
        $transfers = $this->getDoctrine()->getRepository(Transfer::class)
            ->getLatest($seizoen, array(Transfer::ADMINTRANSFER, Transfer::USERTRANSFER), 20);
        $transferRepo = $this->getDoctrine()->getRepository(Transfer::class);

        return array(
            'periode' => $periode,
            'seizoen' => $seizoen,
            'nieuws' => $nieuws,
            'stand' => $stand,
            'shadowstandingsById' => $shadowStandingsById,
            'wedstrijden' => $wedstrijden,
            'periodestand' => $periodestand,
            'transferpuntenPeriode' => $transferSaldo,
            'zegestand' => $zeges,
            'zegesInPeriode' => $zegesInPeriode,
            'drafts' => $draft,
            'transfers' => $transfers,
            'transferRepo' => $transferRepo
        );
    }
}