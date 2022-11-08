<?php declare(strict_types=1);

namespace App\Controller;

use App\Entity\Periode;
use App\Entity\Ploeg;
use App\Entity\Seizoen;
use App\Entity\Transfer;
use App\Entity\Uitslag;
use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/{seizoen}/uitslag")
 */
class UitslagController extends AbstractController
{
    /**
     * @Route ("/periodes/{periode}", name="uitslag_periodes")
     *
     * @ParamConverter ("seizoen", options={"mapping": {"seizoen": "slug"}})
     *
     * @Template ()
     *
     * @return ((Periode|mixed)[]|Periode|Seizoen|\Doctrine\Persistence\ObjectRepository|mixed)[]
     *
     * @psalm-return array{list: mixed, seizoen: Seizoen, periodes: array<Periode>, periode: Periode, transferpoints: array, positionCount: array, transferRepo: \Doctrine\Persistence\ObjectRepository<Transfer>}
     */
    public function periodesAction(Seizoen $seizoen, Periode $periode): array
    {
        $em = $this->getDoctrine()->getManager();
        $list = $this->getDoctrine()->getRepository(Uitslag::class)->getPuntenByPloegForPeriode($periode, $seizoen);
        $periodes = $this->getDoctrine()->getRepository(Periode::class)->findBy(['seizoen' => $seizoen]);

        $gainedTransferpoints = [];
        foreach ($em->getRepository(Uitslag::class)
                     ->getPuntenByPloegForUserTransfersWithoutLoss($seizoen, $periode->getStart(), $periode->getEind()) as $teamResult) {
            $gainedTransferpoints[$teamResult['id']] = $teamResult['punten'];
        }
        $lostDraftPoints = [];
        foreach ($em->getRepository(Uitslag::class)->getLostDraftPuntenByPloeg($seizoen, $periode->getStart(), $periode->getEind()) as $teamResult) {
            if ($teamResult instanceof Ploeg) {
                $lostDraftPoints[$teamResult->getId()] = $teamResult->getPunten();
            } else {
                $lostDraftPoints[$teamResult['id']] = $teamResult['punten'];
            }
        }
        $transferSaldo = [];
        foreach ($gainedTransferpoints as $teamId => $gainedPoints) {
            $transferSaldo[$teamId] = $gainedPoints - $lostDraftPoints[$teamId];
        }
        $zegesInPeriode = [];
        foreach ($em->getRepository(Uitslag::class)->getCountForPosition($seizoen, 1, $periode->getStart(), $periode->getEind()) as $teamResult) {
            $zegesInPeriode[$teamResult[0]->getId()] = $teamResult['freqByPos'];
        }

        return [
            'list' => $list,
            'seizoen' => $seizoen,
            'periodes' => $periodes,
            'periode' => $periode,
            'transferpoints' => $transferSaldo,
            'positionCount' => $zegesInPeriode,
            'transferRepo' => $em->getRepository(Transfer::class),];
    }

    /**
     * @Route ("/posities/{positie}", name="uitslag_posities")
     *
     * @ParamConverter ("seizoen", options={"mapping": {"seizoen": "slug"}})
     *
     * @Template ()
     *
     * @param mixed $positie
     *
     * @return (Seizoen|mixed)[]
     *
     * @psalm-return array{list: mixed, seizoen: Seizoen, positie: mixed}
     */
    public function positiesAction(Request $request, Seizoen $seizoen, $positie = 1): array
    {
        $list = $this->getDoctrine()->getRepository(Uitslag::class)->getCountForPosition($seizoen, $positie);
        return ['list' => $list, 'seizoen' => $seizoen, 'positie' => $positie];
    }

    /**
     * @Route ("/draft-klassement", name="uitslag_draft")
     *
     * @ParamConverter ("seizoen", options={"mapping": {"seizoen": "slug"}})
     *
     * @return (Seizoen|mixed)[]
     *
     * @psalm-return array{list: mixed, seizoen: Seizoen}
     */
    public function viewByDraftTransferAction(Seizoen $seizoen): array
    {
        $list = $this->getDoctrine()->getRepository(Uitslag::class)->getPuntenByPloegForDraftTransfers($seizoen);
        return ['list' => $list, 'seizoen' => $seizoen];
    }

    /**
     * @Route ("/transfer-klassement", name="uitslag_transfers")
     *
     * @ParamConverter ("seizoen", options={"mapping": {"seizoen": "slug"}})
     *
     * @psalm-return array{list: mixed, seizoen: mixed}
     */
    public function viewByUserTransferAction(Seizoen $seizoen): array
    {
        $seizoen = $this->getDoctrine()->getRepository(Seizoen::class)->findBySlug($seizoen);
        $list = $this->getDoctrine()->getRepository(Uitslag::class)->getPuntenByPloegForUserTransfers($seizoen);
        return ['list' => $list, 'seizoen' => $seizoen];
    }

    /**
     * @Route ("/overzicht", name="uitslag_overview")
     *
     * @ParamConverter ("seizoen", options={"mapping": {"seizoen": "slug"}})
     *
     * @Template ()
     *
     * @return (Seizoen|\Doctrine\ORM\EntityRepository|array|mixed)[]
     *
     * @psalm-return array{seizoen: Seizoen, transfer: mixed, shadowgained: array, shadowlost: array, stand: mixed, draft: mixed, transferRepo: \Doctrine\ORM\EntityRepository<Transfer>, bestTransfers: array}
     */
    public function overviewAction(Request $request, Seizoen $seizoen): array
    {
        /** @var EntityManager $em */
        $em = $this->get('doctrine');
        $uitslagRepo = $em->getRepository(Uitslag::class);
        $transfer = $uitslagRepo->getPuntenByPloegForUserTransfers($seizoen);

        $gained = [];
        foreach ($uitslagRepo->getPuntenByPloegForUserTransfersWithoutLoss($seizoen) as $teamResult) {
            $gained[$teamResult['id']] = $teamResult['punten'];
        }
        $lost = [];
        foreach ($uitslagRepo->getLostDraftPuntenByPloeg($seizoen) as $teamResult) {
            if ($teamResult instanceof Ploeg) {
                $lost[$teamResult->getId()] = $teamResult->getPunten();
            } else {
                $lost[$teamResult['id']] = $teamResult['punten'];
            }
        }
        $stand = $uitslagRepo->getPuntenByPloeg($seizoen);
        $draft = $uitslagRepo->getPuntenByPloegForDraftTransfers($seizoen);
        $transferRepo = $em->getRepository(Transfer::class);

        $bestTransfers = array_slice($uitslagRepo->getBestTransfers($seizoen), 0, 50);

        return [
            'seizoen' => $seizoen,
            'transfer' => $transfer,
            'shadowgained' => $gained,
            'shadowlost' => $lost,
            'stand' => $stand,
            'draft' => $draft,
            'transferRepo' => $transferRepo,
            'bestTransfers' => $bestTransfers,];
    }
}
