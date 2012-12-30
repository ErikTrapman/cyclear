<?php

namespace Cyclear\GameBundle\Validator\Constraints;

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

    public function isValid($value, Constraint $constraint)
    {
        if (null === $value->getRennerIn()) {
            $this->setMessage( "Onbekende renner opgegeven" );
            return false;
        }
        $periode = $this->em->getRepository("CyclearGameBundle:Periode")->getCurrentPeriode();
        $transferCount = $this->em->getRepository("CyclearGameBundle:Transfer")
            ->getTransferCountForUserTransfer($value->getPloeg(), $periode->getStart(), $periode->getEind());
        if ($transferCount >= $periode->getTransfers()) {
            $this->setMessage($constraint->message, array("%max%" => $periode->getTransfers()));
            return false;
        }
        $rennerPloeg = $this->em->getRepository("CyclearGameBundle:Renner")->getPloeg($value->getRennerIn(), $value->getSeizoen());
        if (null !== $rennerPloeg) {
            $this->setMessage($value->getRennerIn()->getNaam()." heeft al een ploeg");
            return false;
        }
        if($value->getSeizoen()->getClosed()){
            $this->setMessage("Het seizoen ".$value->getSeizoen()." is gesloten");
            return false;
        }
        return true;
    }
}
