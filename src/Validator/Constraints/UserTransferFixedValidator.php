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

use App\Entity\Renner;
use App\Entity\Transfer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class UserTransferFixedValidator extends ConstraintValidator
{
    /**
     * @var int
     */
    private $maxTransfers;

    private $em;

    /**
     * UserTransferFixedValidator constructor.
     * @param $em
     * @param $maxTransfers
     *
     * TODO write tests!!
     */
    public function __construct(EntityManagerInterface $em, $maxTransfers)
    {
        $this->em = $em;
        $this->maxTransfers = $maxTransfers;
    }

    /**
     * @param UserTransfer $value
     * @return bool
     */
    public function validate($value, Constraint $constraint)
    {
        if (null === $value->getRennerIn() || null === $value->getRennerUit()) {
            $this->context->addViolation('Onbekende renner opgegeven.');
        }
        if ($value->getSeizoen()->getClosed()) {
            $this->context->addViolation('Het seizoen ' . $value->getSeizoen() . ' is gesloten.');
        }
        $rennerPloeg = $this->em->getRepository(Renner::class)
            ->getPloeg($value->getRennerIn(), $value->getSeizoen());
        if (null !== $rennerPloeg) {
            $this->context->addViolation($value->getRennerIn()->getNaam() . ' heeft al een ploeg.');
        }
        $this->doSpecificValidate($value);
    }

    /**
     * @param UserTransfer $value
     */
    protected function doSpecificValidate($value)
    {
        $seizoen = $value->getSeizoen();

        $now = clone $value->getDatum();
        $now->setTime(0, 0, 0);

        $seasonStart = clone $seizoen->getStart();
        $seasonStart->setTime(0, 0, 0);
        if ($now < $seasonStart) {
            $this->context->addViolation('Het huidige seizoen staat nog geen transfers toe.');
        }
        $seasonEnd = clone $seizoen->getEnd();
        $seasonEnd->setTime(0, 0, 0);
        if ($now > $seasonEnd) {
            $this->context->addViolation('Het huidige seizoen staat geen transfers meer toe.');
        }
        $transferCount = $this->em->getRepository(Transfer::class)
            ->getTransferCountForUserTransfer($value->getPloeg(), $seasonStart, $seasonEnd);
        if ($transferCount >= $this->maxTransfers) {
            $this->context->addViolation('Je zit op het maximaal aantal transfers van ' . $this->maxTransfers . '.');
        }
    }
}
