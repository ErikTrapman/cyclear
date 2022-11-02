<?php declare(strict_types=1);

namespace App\Validator\Constraints;

use App\Entity\Renner;
use App\Entity\Transfer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class UserTransferFixedValidator extends ConstraintValidator
{
    /**
     * TODO write tests!!
     */
    public function __construct(private EntityManagerInterface $em, private int $maxTransfers)
    {
    }

    /**
     * @param \App\Form\Entity\UserTransfer $value
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

    protected function doSpecificValidate(\App\Form\Entity\UserTransfer $value)
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
