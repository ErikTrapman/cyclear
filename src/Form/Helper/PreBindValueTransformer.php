<?php declare(strict_types=1);

namespace App\Form\Helper;

class PreBindValueTransformer
{
    /**
     * Transform a posted value in a PRE_BIND listener to it's original type.
     * E.g. a company-id gets transformed to it's corresponding Company-entity,
     * @param mixed $data
     * @param mixed $field
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
