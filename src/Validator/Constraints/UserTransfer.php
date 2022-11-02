<?php declare(strict_types=1);

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class UserTransfer extends Constraint
{
    public $message = 'Je zit op het maximaal aantal transfers van %max% voor deze periode';

    public $entity;

    public $property;

    public function validatedBy()
    {
        return UserTransferFixedValidator::class;
    }

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
