<?php

namespace Cyclear\GameBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class UitslagNew extends Constraint {
    
    public function validatedBy() 
    {
        return 'uitslag_new';
    }
    
    public function getTargets() 
    {
        return self::CLASS_CONSTRAINT;
    }

}
