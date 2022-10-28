<?php declare(strict_types=1);
/*
 * This file is part of the Cyclear-game package.
 *
 * (c) Erik Trapman <veggatron@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Repository;

use App\CQRanking\Exception\CyclearGameBundleCQException;
use App\Entity\Wedstrijd;
use Doctrine\ORM\EntityRepository;

class WedstrijdRepository extends EntityRepository
{
    /**
     * Gets refstage for given $wedstrijd.
     * refStage is the first registered stage for a multiple-days race.
     * Typically the Wedstrijd has a name like 'Dubai Tour, General classification'
     * We use that to lookup 'Dubai Tour, Stage 1' or 'Dubai Tour, Prologue'.
     *
     * @return Wedstrijd
     * @throws CyclearGameBundleCQException
     */
    public function getRefStage(Wedstrijd $wedstrijd)
    {
        $parts = explode(',', $wedstrijd->getNaam());
        if (empty($parts)) {
            throw new CyclearGameBundleCQException('Unable to lookup refStage for ' . $wedstrijd->getNaam());
        }
        $transliterator = \Transliterator::createFromRules(':: NFD; :: [:Nonspacing Mark:] Remove; :: NFC;', \Transliterator::FORWARD);
        $stage = $transliterator->transliterate($parts[0]);
        $prologue = $transliterator->transliterate($parts[0]);
        $stage1 = $stage . ', Stage 1%';
        $prologue = $prologue . ', Prologue%';
        $qb = $this->createQueryBuilder('w');
        $qb->where('w.seizoen = :seizoen')->andWhere(
            $qb->expr()->orX(
                $qb->expr()->like('w.naam', ':stage1'),
                $qb->expr()->like('w.naam', ':prol'))
        );
        $qb->setParameters(['seizoen' => $wedstrijd->getSeizoen(), 'stage1' => $stage1, 'prol' => $prologue]);
        $res = $qb->getQuery()->getResult();
        if (0 === count($res)) {
            // try again with 'Stage 2' as we do not always register stage 1 if it's a TTT. It's the best we can get.
            $qb = $this->createQueryBuilder('w');
            $qb->where('w.seizoen = :seizoen')->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->like('w.naam', ':stage2'))
            );
            $qb->setParameters(['seizoen' => $wedstrijd->getSeizoen(), 'stage2' => sprintf('%s, Stage 2%%', $stage)]);
            $res = $qb->getQuery()->getResult();
            if (0 === count($res)) {
                throw new CyclearGameBundleCQException('Unable to lookup refStage for ' . $wedstrijd->getNaam() . '. Have ' . count($res) . ' results');
            }
            return $res[0];
        }
        return $res[0];
    }

    /**
     * Gets all stages for given $wedstrijd.
     * Typically the Wedstrijd has a name like 'Dubai Tour, General classification'
     * We use that to lookup 'Dubai Tour, Stage *' or 'Dubai Tour, Prologue'.
     *
     * @return Wedstrijd[]
     */
    public function getRefStages(Wedstrijd $wedstrijd)
    {
        $parts = explode(',', $wedstrijd->getNaam());
        if (empty($parts)) {
            throw new CyclearGameBundleCQException('Unable to lookup refStage for ' . $wedstrijd->getNaam());
        }
        $transliterator = \Transliterator::createFromRules(':: NFD; :: [:Nonspacing Mark:] Remove; :: NFC;', \Transliterator::FORWARD);
        $stage = $transliterator->transliterate($parts[0]);
        $prologue = $transliterator->transliterate($parts[0]);
        $stages = $stage . ', Stage%';
        $prologue = $prologue . ', Prologue%';
        $qb = $this->createQueryBuilder('w');
        $qb->where('w.seizoen = :seizoen')->andWhere(
            $qb->expr()->orX(
                $qb->expr()->like('w.naam', ':stages'),
                $qb->expr()->like('w.naam', ':prol'))
        );
        $qb->setParameters(['seizoen' => $wedstrijd->getSeizoen(), 'stages' => $stages, 'prol' => $prologue]);
        return $qb->getQuery()->getResult();
    }
}
