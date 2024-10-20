<?php declare(strict_types=1);

namespace App\Validator\Constraints;

use App\Repository\RennerRepository;
use App\Repository\TransferRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class UserTransferFixedValidator extends ConstraintValidator
{
    public function __construct(
        private readonly RennerRepository $rennerRepository,
        private readonly TransferRepository $transferRepository,
        private readonly int $maxTransfers,
    ) {
    }

    public function validate($value, Constraint $constraint)
    {
        if (null === $value->getRennerIn() || null === $value->getRennerUit()) {
            $this->context->addViolation('Onbekende renner opgegeven.');
            return;
        }
        if ($value->getSeizoen()->getClosed()) {
            $this->context->addViolation('Het seizoen ' . $value->getSeizoen() . ' is gesloten.');
            return;
        }
        $rennerPloeg = $this->rennerRepository->getPloeg($value->getRennerIn(), $value->getSeizoen());
        if (null !== $rennerPloeg) {
            $this->context->addViolation($value->getRennerIn()->getNaam() . ' heeft al een ploeg.');
        }
        $this->doSpecificValidate($value);
    }

    protected function doSpecificValidate(\App\Form\Entity\UserTransfer $value): void
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
        $transferCount = $this->transferRepository->getTransferCountForUserTransfer($value->getPloeg(), $seasonStart, $seasonEnd);
        if ($transferCount >= $this->maxTransfers) {
            $this->context->addViolation('Je zit op het maximaal aantal transfers van ' . $this->maxTransfers . '.');
        }
    }
}
