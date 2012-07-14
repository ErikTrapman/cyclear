<?php

namespace Cyclear\GameBundle\Validator\Constraints;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;


class UserTransferValidator extends ConstraintValidator {

    private $em;

    public function __construct($em) {
        $this->em = $em;
    }
    
    
    public function isValid($value, Constraint $constraint) {
        if(null === $value){
            return true;
        }
        $periode = $this->em->getRepository("CyclearGameBundle:Periode")->getCurrentPeriode();
        $transferCount = $this->em->getRepository("CyclearGameBundle:Transfer")->getTransferCount($value->getPloeg(), $periode->getStart(), $periode->getEind());
        if($transferCount == $periode->getTransfers()){
            $this->setMessage($constraint->message , array("%max%" => $periode->getTransfers()));
            return false;
        }
        if( null !== $value->getRennerIn()->getPloeg() ){
            $this->setMessage( $value->getRennerIn(). " heeft al een ploeg" );
            return false;
        }
        return true;
    }

}
