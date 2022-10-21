<?php

/*
 * This file is part of the CQ-ranking parser package.
 *
 * (c) Erik Trapman <veggatron@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Form\DataTransformer;

use App\CQRanking\Parser\Strategy\AbstractStrategy;
use App\CQRanking\Parser\Strategy\Y2012\Stage;
use App\Entity\Renner;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class ConstantToStrategyClassTransformer implements DataTransformerInterface
{

    /**
     * 
     * @param Renner $value
     * @return string
     */
    public function transform($value)
    {
        if ($value === null) {
            return $value;
        }
        if ($value instanceof AbstractStrategy) {
            return get_class($value);
        }
        return null;
    }

    /**
     * 
     * @param string $value
     * @return string|Stage
     * @throws TransformationFailedException
     */
    public function reverseTransform($value)
    {
        if ($value === null) {
            return null;
        }
        if (!class_exists($value)) {
            throw new TransformationFailedException("Unknown class ".$value);
        }
        $instance = new $value;
        if (!$instance instanceof AbstractStrategy) {
            throw new TransformationFailedException("Invalid class ".$value);
        }
        return $instance;
    }
}
?>
