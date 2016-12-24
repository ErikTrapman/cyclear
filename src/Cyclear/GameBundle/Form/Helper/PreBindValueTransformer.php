<?php

/*
 * This file is part of the Cyclear-game package.
 *
 * (c) Erik Trapman <veggatron@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cyclear\GameBundle\Form\Helper;

class PreBindValueTransformer
{

    /**
     *
     * Transform a posted value in a PRE_BIND listener to it's original type.
     * E.g. a company-id gets transformed to it's corresponding Company-entity,
     *
     * @param type $data
     * @param type $field
     * @return type
     */
    public function transformPostedValue($data, $field)
    {
        foreach ($field->getConfig()->getViewTransformers() as $transformer) {
            $data = $transformer->reverseTransform($data);
        }
        foreach ($field->getConfig()->getModelTransformers() as $transformer) {
            $data = $transformer->reverseTransform($data);
        }
        return $data;
    }
}