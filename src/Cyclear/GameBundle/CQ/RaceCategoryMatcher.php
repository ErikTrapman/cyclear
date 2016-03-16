<?php

/*
 * This file is part of the Cyclear-game package.
 *
 * (c) Erik Trapman <veggatron@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cyclear\GameBundle\CQ;


use Cyclear\GameBundle\CQ\Exception\CyclearGameBundleCQException;
use Cyclear\GameBundle\Entity\UitslagType;
use Cyclear\GameBundle\Entity\Wedstrijd;
use Doctrine\ORM\EntityManager;

class RaceCategoryMatcher
{

    /**
     * @var EntityManager
     */
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param string $category
     * @return UitslagType
     */
    public function getUitslagTypeAccordingToCategory($category)
    {
        $repo = $this->em->getRepository('CyclearGameBundle:UitslagType');
        /** @var UitslagType $uitslagType */
        foreach ($repo->findAll() as $uitslagType) {
            $pattern = '/^(' . $this->getPregPattern($uitslagType->getAutomaticResolvingCategories()) . ')$/';
            $match = preg_match($pattern, $category);
            if (0 !== $match) {
                return $uitslagType;
            }
        }
        return null;
    }

    /**
     * Tells if given Wedstrijd needs a reference-stage.
     * True if wedstrijdname contains 'general classification'
     *
     * @param $string
     * @return bool
     */
    public function needsRefStage(Wedstrijd $wedstrijd)
    {
        $in = 'generalclassification';
        $string = $wedstrijd->getNaam();
        $string = strtolower($string);
        $string = str_replace(' ', '', $string);
        if (false !== stripos($string, $in)) {
            return true;
        }
        return false;
    }

    /**
     * Gets refstage for given $wedstrijd.
     * Typically the Wedstrijd has a name like 'Dubai Tour, General classification'
     * We use that to lookup 'Dubai Tour, Stage 1' or 'Dubai Tour, Prologue'
     *
     * @param Wedstrijd $wedstrijd
     * @return Wedstrijd
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
        $qb = $this->em->getRepository('CyclearGameBundle:Wedstrijd')->createQueryBuilder('w');
        $qb->where('w.seizoen = :seizoen')->andWhere(
            $qb->expr()->orX(
                $qb->expr()->like('w.naam', ":stage1"),
                $qb->expr()->like('w.naam', ":prol"))
        );
        $qb->setParameters(['seizoen' => $wedstrijd->getSeizoen(), 'stage1' => $stage1, 'prol' => $prologue]);
        $res = $qb->getQuery()->getResult();
        if (0 === count($res)) {
            // try again with 'Stage 2' as we do not always register stage 1 if it's a TTT. It's the best we can get.
            $qb = $this->em->getRepository('CyclearGameBundle:Wedstrijd')->createQueryBuilder('w');
            $qb->where('w.seizoen = :seizoen')->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->like('w.naam', ":stage2"))
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
     * @param string $string
     */
    public function getPregPattern($string)
    {
        return implode('|', explode(',', str_replace(' ', '', $string)));
    }

}