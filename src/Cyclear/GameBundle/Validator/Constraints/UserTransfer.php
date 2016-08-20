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

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class UserTransfer extends Constraint
{

    public $message = "Je zit op het maximaal aantal transfers van %max% voor deze periode";

    public $entity;

    public $property;

    public function validatedBy()
    {
        return 'user_transfer';
    }

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

}
