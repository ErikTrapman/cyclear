<?php declare(strict_types=1);

namespace App\Controller;

use App\Entity\Nieuws;
use App\Entity\Seizoen;
use App\Entity\Transfer;
use App\Repository\PeriodeRepository;
use App\Repository\TransferRepository;
use App\Repository\UitslagRepository;
use App\Repository\WedstrijdRepository;
use Doctrine\Persistence\ManagerRegistry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/{seizoen}")
 */
class DefaultController extends AbstractController
{
    public function __construct(
        private readonly ManagerRegistry $doctrine,
        private readonly PeriodeRepository $periodeRepository,
        private readonly UitslagRepository $uitslagRepository,
        private readonly WedstrijdRepository $wedstrijdRepository,
        private readonly TransferRepository $transferRepository
    ) {
    }

    /**
     * @Route("/", name="game")
     * @ParamConverter("seizoen", options={"mapping": {"seizoen": "slug"}})
     * @Template()
     */
    public function indexAction(Seizoen $seizoen): array
    {
        $periode = $this->periodeRepository->getCurrentPeriode($seizoen);
        $refDate = $periode ? $periode->getStart() : new \DateTime('today');

        $shadowStandingsById = [];
        foreach ($this->uitslagRepository->getPuntenByPloeg($seizoen, null, $refDate) as $key => $value) {
            $value['position'] = $key + 1;
            $shadowStandingsById[$value[0]->getId()] = $value;
        }
        $gainedTransferPoints = [];
        $lostDraftPoints = [];
        $zegesInPeriode = [];
        if ($periode) {
            foreach ($this->uitslagRepository->getPuntenByPloegForUserTransfersWithoutLoss($seizoen, $periode->getStart(), $periode->getEind()) as $teamResult) {
                $gainedTransferPoints[$teamResult['id']] = $teamResult['punten'];
            }
            foreach ($this->uitslagRepository->getLostDraftPuntenByPloeg($seizoen, $periode->getStart(), $periode->getEind()) as $teamResult) {
                $lostDraftPoints[$teamResult->getId()] = $teamResult->getPunten();
            }
            foreach ($this->uitslagRepository->getCountForPosition($seizoen, 1, $periode->getStart(), $periode->getEind()) as $teamResult) {
                $zegesInPeriode[$teamResult[0]->getId()] = $teamResult['freqByPos'];
            }
        }
        $transferSaldo = [];
        foreach ($gainedTransferPoints as $teamId => $gainedPoints) {
            $transferSaldo[$teamId] = $gainedPoints - $lostDraftPoints[$teamId];
        }

        return [
            'drafts' => $this->uitslagRepository->getPuntenByPloegForDraftTransfers($seizoen),
            'nieuws' => $this->doctrine->getRepository(Nieuws::class)->findBy(['seizoen' => $seizoen], ['id' => 'DESC'], 1)[0] ?? null,
            'periode' => $periode,
            'periodestand' => $periode ? $this->uitslagRepository->getPuntenByPloegForPeriode($periode, $seizoen) : [],
            'seizoen' => $seizoen,
            'shadowstandingsById' => $shadowStandingsById,
            'stand' => $this->uitslagRepository->getPuntenByPloeg($seizoen),
            'transferpuntenPeriode' => $transferSaldo,
            'transferRepo' => $this->transferRepository,
            'transfers' => $this->transferRepository->getLatest($seizoen, [Transfer::ADMINTRANSFER, Transfer::USERTRANSFER], 20),
            'wedstrijden' => $this->wedstrijdRepository->getLatest($seizoen, 12),
            'zegesInPeriode' => $zegesInPeriode,
            'zegestand' => $this->uitslagRepository->getCountForPosition($seizoen, 1),
        ];
    }
}
