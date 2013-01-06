<?php

namespace Cyclear\GameBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class UitslagNewValidator extends ConstraintValidator
{
    public $groups = array('url');
    
    public function isValid($value, Constraint $constraint)
    {
        var_dump($value);
        die;
    }
}
