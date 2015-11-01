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
        $stage1 = $parts[0] . ', Stage 1';
        $prologue = $parts[0] . ', Prologue';
        $qb = $this->em->getRepository('CyclearGameBundle:Wedstrijd')->createQueryBuilder('w');
        $qb->where('w.seizoen = :seizoen')->andWhere(
            $qb->expr()->orX($qb->expr()->like('w.naam', "'" . $stage1 . "%'"), $qb->expr()->like('w.naam', "'" . $prologue . "%'"))
        );
        $qb->setParameter('seizoen', $wedstrijd->getSeizoen());
        $res = $qb->getQuery()->getResult();
        if (count($res) !== 1) {
            throw new CyclearGameBundleCQException('Unable to lookup refStage for ' . $wedstrijd->getNaam() . '. Have ' . count($res) . ' results');
        }
        return $res[0];
    }


    /**
     * @param string $string
     */
    public function getPregPattern($string)
    {
        return implode('|', explode(',', $string));
    }

}