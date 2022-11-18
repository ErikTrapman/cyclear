<?php declare(strict_types=1);

namespace App\EntityManager;

use App\Entity\Contract;
use App\Entity\Ploeg;
use App\Entity\Renner;
use App\Entity\Seizoen;
use App\Repository\ContractRepository;

class ContractManager
{
    public function __construct(
        private readonly ContractRepository $contractRepository,
    ) {
    }

    public function releaseRenner(Renner $renner, Seizoen $seizoen, \DateTime $einddatum): ?Contract
    {
        if ($currentContract = $this->contractRepository->getCurrentContract($renner, $seizoen)) {
            $currentContract->setEind($einddatum);
        }
        return $currentContract;
    }

    public function createContract(Renner $renner, Ploeg $ploeg, Seizoen $seizoen, \DateTime $datum): Contract
    {
        $c = new Contract();
        $c->setPloeg($ploeg);
        $c->setRenner($renner);
        $c->setSeizoen($seizoen);
        $c->setStart($datum);
        return $c;
    }
}
