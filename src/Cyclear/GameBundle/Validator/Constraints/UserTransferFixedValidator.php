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

class UserTransferFixedValidator extends UserTransferValidator
{
    /**
     * @var int
     */
    private $maxTransfers;

    public function __construct($em, $maxTransfers)
    {
        parent::__construct($em);
        $this->maxTransfers = $maxTransfers;
    }

    /**
     * @param $value
     * @param \DateTime $start
     * @param \DateTime $end
     * @param $maxAmount
     */
    protected function testMaxTransfers($value, \DateTime $start, \DateTime $end, $maxAmount)
    {
        $transferCount = $this->em->getRepository("CyclearGameBundle:Transfer")
            ->getTransferCountForUserTransfer($value->getPloeg(), $start, $end);
        if ($transferCount >= $this->maxTransfers) {
            $this->context->addViolation("Je zit op het maximaal aantal transfers van " . $this->maxTransfers);
        }
    }


}