<?php declare(strict_types=1);

namespace App\Form\DataTransformer;

use App\CQRanking\Parser\Strategy\AbstractStrategy;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class ConstantToStrategyClassTransformer implements DataTransformerInterface
{
    public function transform($value): ?string
    {
        if ($value === null) {
            return $value;
        }
        if ($value instanceof AbstractStrategy) {
            return get_class($value);
        }
        return null;
    }

    public function reverseTransform($value): ?AbstractStrategy
    {
        if ($value === null) {
            return null;
        }
        if (!class_exists($value)) {
            throw new TransformationFailedException('Unknown class ' . $value);
        }
        $instance = new $value();
        if (!$instance instanceof AbstractStrategy) {
            throw new TransformationFailedException('Invalid class ' . $value);
        }
        return $instance;
    }
}
