<?php

/*
 * This file is part of the Cyclear-game package.
 *
 * (c) Erik Trapman <veggatron@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cyclear\GameBundle\Validator\Constraints;

use Cyclear\GameBundle\Entity\Transfer;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class UserTransferValidator extends ConstraintValidator
{
    private $em;

    public function __construct($em)
    {
        $this->em = $em;
    }

    /**
     * @param $value
     * @param Constraint $constraint
     * @return bool
     */
    public function validate($value, Constraint $constraint)
    {
        if (null === $value->getRennerIn() || null === $value->getRennerUit()) {
            $this->context->addViolationAt('renner', "Onbekende renner opgegeven");
        }
        if ($value->getSeizoen()->getClosed()) {
            $this->context->addViolation("Het seizoen " . $value->getSeizoen() . " is gesloten");
        }
        $periode = $this->em->getRepository("CyclearGameBundle:Periode")->getCurrentPeriode();
        $now = $value->getDatum();
        if ($now < $periode->getStart()) {
            $this->context->addViolation("De huidige periode staat nog geen transfers toe");
        }
        if ($now > $periode->getEind()) {
            $this->context->addViolation("De huidige periode staat geen transfers meer toe");
        }
        $transferCount = $this->em->getRepository("CyclearGameBundle:Transfer")->getTransferCountForUserTransfer($value->getPloeg(), $periode->getStart(), $periode->getEind());
        if ($transferCount >= $periode->getTransfers()) {
            $this->context->addViolation("Je zit op het maximaal aantal transfers van " . $periode->getTransfers() . " voor deze periode");
        }
        $rennerPloeg = $this->em->getRepository("CyclearGameBundle:Renner")->getPloeg($value->getRennerIn(), $value->getSeizoen());
        if (null !== $rennerPloeg) {
            $this->context->addViolation($value->getRennerIn()->getNaam() . " heeft al een ploeg");
        }
    }
}
