<?php declare(strict_types=1);

namespace App\EntityManager;

use App\Entity\Contract;
use Doctrine\ORM\EntityManagerInterface;

class ContractManager
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @return true
     */
    public function releaseRenner(\App\Entity\Renner $renner, $seizoen, \DateTime $einddatum): bool
    {
        $currentContract = $this->em->getRepository(Contract::class)->getCurrentContract($renner, $seizoen);
        if (null === $currentContract) {
            return true;
        }
        $currentContract->setEind($einddatum);
        $this->em->persist($currentContract);
        return true;
    }

    public function createContract(\App\Entity\Renner $renner, \App\Entity\Ploeg $ploeg, $seizoen, \DateTime $datum): Contract
    {
        $c = new \App\Entity\Contract();
        $c->setPloeg($ploeg);
        $c->setRenner($renner);
        $c->setSeizoen($seizoen);
        $c->setStart($datum);
        $this->em->persist($c);
        return $c;
    }
}
