<?php declare(strict_types=1);

namespace App\Controller;

use App\Entity\Periode;
use App\Entity\Ploeg;
use App\Entity\Seizoen;
use App\Entity\Transfer;
use App\Entity\Uitslag;
use App\Repository\PeriodeRepository;
use App\Repository\TransferRepository;
use App\Repository\UitslagRepository;
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
    public function __construct(
        private readonly UitslagRepository $uitslagRepository,
        private readonly PeriodeRepository $periodeRepository,
        private readonly TransferRepository $transferRepository,
    ) {
    }

    /**
     * @Route("/periodes/{periode}", name="uitslag_periodes")
     * @ParamConverter("seizoen", options={"mapping": {"seizoen": "slug"}})
     * @Template()
     */
    public function periodesAction(Seizoen $seizoen, Periode $periode): array
    {
        $list = $this->uitslagRepository->getPuntenByPloegForPeriode($periode, $seizoen);
        $periodes = $this->periodeRepository->findBy(['seizoen' => $seizoen]);

        $gainedTransferpoints = [];
        foreach ($this->uitslagRepository->getPuntenByPloegForUserTransfersWithoutLoss($seizoen, $periode->getStart(), $periode->getEind()) as $teamResult) {
            $gainedTransferpoints[$teamResult['id']] = $teamResult['punten'];
        }
        $lostDraftPoints = [];
        foreach ($this->uitslagRepository->getLostDraftPuntenByPloeg($seizoen, $periode->getStart(), $periode->getEind()) as $teamResult) {
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
        foreach ($this->uitslagRepository->getCountForPosition($seizoen, 1, $periode->getStart(), $periode->getEind()) as $teamResult) {
            $zegesInPeriode[$teamResult[0]->getId()] = $teamResult['freqByPos'];
        }

        return [
            'list' => $list,
            'seizoen' => $seizoen,
            'periodes' => $periodes,
            'periode' => $periode,
            'transferpoints' => $transferSaldo,
            'positionCount' => $zegesInPeriode,
            'transferRepo' => $this->transferRepository,
        ];
    }

    /**
     * @Route("/posities/{positie}", name="uitslag_posities")
     * @ParamConverter("seizoen", options={"mapping": {"seizoen": "slug"}})
     * @Template()
     * @param mixed $positie
     */
    public function positiesAction(Request $request, Seizoen $seizoen, $positie = 1): array
    {
        $list = $this->uitslagRepository->getCountForPosition($seizoen, $positie);
        return ['list' => $list, 'seizoen' => $seizoen, 'positie' => $positie];
    }

    /**
     * @Route("/draft-klassement", name="uitslag_draft")
     * @ParamConverter("seizoen", options={"mapping": {"seizoen": "slug"}})
     */
    public function viewByDraftTransferAction(Seizoen $seizoen): array
    {
        $list = $this->uitslagRepository->getPuntenByPloegForDraftTransfers($seizoen);
        return ['list' => $list, 'seizoen' => $seizoen];
    }

    /**
     * @Route("/transfer-klassement", name="uitslag_transfers")
     * @ParamConverter("seizoen", options={"mapping": {"seizoen": "slug"}})
     */
    public function viewByUserTransferAction(Seizoen $seizoen): array
    {
        $list = $this->uitslagRepository->getPuntenByPloegForUserTransfers($seizoen);
        return ['list' => $list, 'seizoen' => $seizoen];
    }

    /**
     * @Route("/overzicht", name="uitslag_overview")
     * @ParamConverter("seizoen", options={"mapping": {"seizoen": "slug"}})
     * @Template()
     */
    public function overviewAction(Request $request, Seizoen $seizoen): array
    {
        $transfer = $this->uitslagRepository->getPuntenByPloegForUserTransfers($seizoen);

        $gained = [];
        foreach ($this->uitslagRepository->getPuntenByPloegForUserTransfersWithoutLoss($seizoen) as $teamResult) {
            $gained[$teamResult['id']] = $teamResult['punten'];
        }
        $lost = [];
        foreach ($this->uitslagRepository->getLostDraftPuntenByPloeg($seizoen) as $teamResult) {
            if ($teamResult instanceof Ploeg) {
                $lost[$teamResult->getId()] = $teamResult->getPunten();
            } else {
                $lost[$teamResult['id']] = $teamResult['punten'];
            }
        }
        // postprocess $transfer
        foreach ($transfer as &$item) {
            $ploegId = (int)$item['id'];
            $lostPoints = array_key_exists($ploegId, $lost) ? $lost[$ploegId] : 0;
            $item['punten_calculated'] = $item['punten'] - $lostPoints;
        }
        uasort($transfer, function ($a, $b) {
            return $b['punten_calculated'] <=> $a['punten_calculated'];
        });

        $stand = $this->uitslagRepository->getPuntenByPloeg($seizoen);
        $draft = $this->uitslagRepository->getPuntenByPloegForDraftTransfers($seizoen);

        $bestTransfers = array_slice($this->uitslagRepository->getBestTransfers($seizoen), 0, 50);

        return [
            'seizoen' => $seizoen,
            'transfer' => $transfer,
            'shadowgained' => $gained,
            'shadowlost' => $lost,
            'stand' => $stand,
            'draft' => $draft,
            'transferRepo' => $this->transferRepository,
            'bestTransfers' => $bestTransfers,
        ];
    }
}
