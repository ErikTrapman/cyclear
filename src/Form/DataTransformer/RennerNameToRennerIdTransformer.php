<?php declare(strict_types=1);

namespace App\Form\DataTransformer;

use App\Entity\Renner;
use App\EntityManager\RennerManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class RennerNameToRennerIdTransformer implements DataTransformerInterface
{
    public function __construct(private EntityManagerInterface $em, private RennerManager $rennerManager)
    {
    }

    public function transform(mixed $value): mixed
    {
        if (null === $value) {
            return '';
        }
        if ($value instanceof Renner) {
            return $this->rennerManager->getRennerSelectorTypeString($value->getCqRanking_id(), $value->getNaam());
        }
        return 'unrecognised value';
    }

    public function reverseTransform(mixed $value): mixed
    {
        if (null === $value) {
            return '';
        }
        if (is_numeric($value)) {
            $cqId = $value;
        } else {
            $cqId = $this->rennerManager->getCqIdFromRennerSelectorTypeString($value);
        }
        $em = $this->em;
        $renner = $em->getRepository(Renner::class)->findOneByCQId($cqId);
        if (null === $renner) {
            throw new TransformationFailedException('Renner ' . $value . ' niet gevonden');
        }
        return $renner;
    }
}
