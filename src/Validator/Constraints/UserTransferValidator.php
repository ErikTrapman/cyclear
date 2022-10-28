<?php declare(strict_types=1);

/*
 * This file is part of the Cyclear-game package.
 *
 * (c) Erik Trapman <veggatron@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Validator\Constraints;

use App\Entity\Periode;
use App\Entity\Renner;
use App\Entity\Transfer;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class UserTransferValidator extends ConstraintValidator
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @param $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @param Transfer $value
     * @return bool
     */
    public function validate($value, Constraint $constraint)
    {
        if (null === $value->getRennerIn() || null === $value->getRennerUit()) {
            $this->context->addViolation('Onbekende renner opgegeven');
        }
        if ($value->getSeizoen()->getClosed()) {
            $this->context->addViolation('Het seizoen ' . $value->getSeizoen() . ' is gesloten');
        }
        $periode = $this->em->getRepository(Periode::class)->getCurrentPeriode();
        $now = clone $value->getDatum();
        $now->setTime(0, 0, 0);
        if ($now < $periode->getStart()) {
            $this->context->addViolation('De huidige periode staat nog geen transfers toe');
        }
        if ($now > $periode->getEind()) {
            $this->context->addViolation('De huidige periode staat geen transfers meer toe');
        }
        $this->testMaxTransfers($value, $periode->getStart(), $periode->getEind(), $periode->getTransfers());
        $rennerPloeg = $this->em->getRepository(Renner::class)->getPloeg($value->getRennerIn(), $value->getSeizoen());
        if (null !== $rennerPloeg) {
            $this->context->addViolation($value->getRennerIn()->getNaam() . ' heeft al een ploeg');
        }
    }

    /**
     * @param $value
     * @param $maxAmount
     */
    protected function testMaxTransfers($value, \DateTime $start, \DateTime $end, $maxAmount)
    {
        $transferCount = $this->em->getRepository(Transfer::class)->getTransferCountForUserTransfer($value->getPloeg(), $start, $end);
        if ($transferCount >= $maxAmount) {
            $this->context->addViolation('Je zit op het maximaal aantal transfers van ' . $maxAmount . ' voor deze periode');
        }
    }
}
