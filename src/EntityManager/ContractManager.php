<?php declare(strict_types=1);

namespace App\EntityManager;

use App\Entity\Contract;
use App\Entity\Ploeg;
use App\Entity\Renner;
use App\Entity\Seizoen;
use Doctrine\ORM\EntityManagerInterface;

class ContractManager
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function releaseRenner(Renner $renner, Seizoen $seizoen, \DateTime $einddatum): bool
    {
        $currentContract = $this->em->getRepository(Contract::class)->getCurrentContract($renner, $seizoen);
        if (null === $currentContract) {
            return true;
        }
        $currentContract->setEind($einddatum);
        $this->em->persist($currentContract);
        return true;
    }

    public function createContract(Renner $renner, Ploeg $ploeg, Seizoen $seizoen, \DateTime $datum): Contract
    {
        $c = new Contract();
        $c->setPloeg($ploeg);
        $c->setRenner($renner);
        $c->setSeizoen($seizoen);
        $c->setStart($datum);
        $this->em->persist($c);
        return $c;
    }
}
