<?php

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
        foreach ($field->getClientTransformers() as $transformer) {
            $data = $transformer->reverseTransform($data);
        }
        foreach ($field->getNormTransformers() as $transformer) {
            $data = $transformer->reverseTransform($data);
        }
        return $data;
    }
}