<?php declare(strict_types=1);

/*
 * This file is part of the Cyclear-game package.
 *
 * (c) Erik Trapman <veggatron@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Form\DataTransformer;

use App\Entity\Renner;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class RennerNameToRennerIdTransformer implements DataTransformerInterface
{
    /**
     * @var Symfony\Bundle\DoctrineBundle\Registry
     */
    private $em;

    private $rennerManager;

    public function __construct(EntityManager $em, $rennerManager)
    {
        $this->em = $em;
        $this->rennerManager = $rennerManager;
    }

    /* (non-PHPdoc)
     * @see Symfony\Component\Form.DataTransformerInterface::transform()
     * // transforms the Issue object to a string
     * @param Renner $value
     */

    public function transform($value)
    {
        if ($value === null) {
            return '';
        }
        if ($value instanceof \App\Entity\Renner) {
            return $this->rennerManager->getRennerSelectorTypeString($value->getCqRanking_id(), $value->getNaam());
        }
        return 'unrecognised value';
    }

    /* (non-PHPdoc)
     * @see Symfony\Component\Form.DataTransformerInterface::reverseTransform()
     *
     */

    public function reverseTransform($value)
    {
        if ($value === null) {
            return '';
        }
        if (is_numeric($value)) {
            $cqId = $value;
        } else {
            $cqId = $this->rennerManager->getCqIdFromRennerSelectorTypeString($value);
        }
        $em = $this->em;
        $renner = $em->getRepository(Renner::class)->findOneByCQId($cqId);
        if ($renner === null) {
            throw new TransformationFailedException('Renner ' . $value . ' niet gevonden');
        }
        return $renner;
    }
}
