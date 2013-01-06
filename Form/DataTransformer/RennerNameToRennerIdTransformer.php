<?php

namespace Cyclear\GameBundle\Form\DataTransformer;

use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\DataTransformerInterface;
use Doctrine\ORM\EntityManager;

class RennerNameToRennerIdTransformer implements DataTransformerInterface {

    /**
     * 
     * @var Symfony\Bundle\DoctrineBundle\Registry
     */
    private $em;

    public function __construct(EntityManager $em) {
        $this->em = $em;
    }

    /* (non-PHPdoc)
     * @see Symfony\Component\Form.DataTransformerInterface::transform()
     * // transforms the Issue object to a string
     * @param Renner $value
     */

    public function transform($value) {
        if ($value === null) {
            return '';
        }
        if ($value instanceof \Cyclear\GameBundle\Entity\Renner) {
            return $value->getCqRanking_id() . ' [' . $value->getNaam() . ']';
        }
        return 'unrecognised value';
    }

    /* (non-PHPdoc)
     * @see Symfony\Component\Form.DataTransformerInterface::reverseTransform()
     * 
     */

    public function reverseTransform($value) {
        if ($value === null) {
            return "";
        }
        if (is_numeric($value)) {
            $cqId = $value;
        } else {
            $rennerString = $value;
            $firstBracket = strpos($rennerString, '[');
            $lastBracket = strpos($rennerString, ']');
            $cqId = trim(substr($rennerString, 0, $firstBracket));
            $name = substr($rennerString, $firstBracket + 1, $lastBracket - $firstBracket - 1);
        }
        $em = $this->em;
        $renner = $em->getRepository('Cyclear\GameBundle\Entity\Renner')->findOneByCQId($cqId);

        if ($renner === null) {
            throw new TransformationFailedException("Renner " . $value . " niet gevonden");
        }
        return $renner;
    }

}