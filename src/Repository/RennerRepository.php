<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\Ploeg;
use App\Entity\Renner;
use App\Entity\Seizoen;
use App\Entity\Transfer;
use App\Entity\Uitslag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class RennerRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly SeizoenRepository $seizoenRepository,
        private readonly ContractRepository $contractRepository,
    ) {
        parent::__construct($registry, Renner::class);
    }

    public function findOneByNaam($naam): object|null
    {
        return $this->findOneBy(['naam' => $naam]);
    }

    public function findOneByCQId($id)
    {
        return $this->findOneBy(['cqranking_id' => $id]);
    }

    public function getPloeg($renner, $seizoen = null)
    {
        $seizoen = $this->resolveSeizoen($seizoen);
        if (is_numeric($renner)) {
            $renner = $this->find($renner);
        }
        $contract = $this->contractRepository->getCurrentContract($renner, $seizoen);
        if (null === $contract) {
            return null;
        }
        return $contract->getPloeg();
    }

    /**
     * @return bool
     */
    public function isDraftTransfer(Renner $renner, Ploeg $ploeg)
    {
        return (bool)$this->_em->getRepository(Transfer::class)->hasDraftTransfer($renner, $ploeg);
    }

    public function getRennersWithPunten($seizoen = null, $excludeWithTeam = false)
    {
        $this->resolveSeizoen($seizoen);
        $puntenQb = $this->_em->getRepository(Uitslag::class)
            ->createQueryBuilder('u')
            ->select('SUM(u.rennerPunten)')
            ->innerJoin('u.wedstrijd', 'w')
            ->where('u.renner = r')
            ->andWhere('w.seizoen = :seizoen')//    ->setParameter('seizoen', $seizoen)
        ;
        $teamQb = $this->contractRepository
            ->createQueryBuilder('c')
            ->select('p.afkorting')
            ->innerJoin('c.ploeg', 'p')
            ->where('c.seizoen = :seizoen')
            ->andWhere('c.eind IS NULL')
            ->andWhere('c.renner = r')// ->setParameter('seizoen', $seizoen)
        ;
        $qb = $this->createQueryBuilder('r')
            ->addSelect('IFNULL((' . $puntenQb->getDQL() . '), 0) AS punten')
            ->leftJoin('r.country', 'cty')->addSelect('cty')
            ->orderBy('punten', 'DESC, r.naam ASC');
        if (true === $excludeWithTeam) {
            $qb->andHaving('IFNULL((' . $teamQb->getDQL() . '), -1) < 0');
        } else {
            $qb->addSelect('(' . $teamQb->getDQL() . ') AS team');
        }
        return $qb->setParameter('seizoen', $seizoen); // ->setMaxResults(20)->getQuery()->getResult();
    }

    private function resolveSeizoen(Seizoen $seizoen = null): Seizoen
    {
        if (null === $seizoen) {
            $seizoen = $this->seizoenRepository->getCurrent();
        }
        return $seizoen;
    }
}
