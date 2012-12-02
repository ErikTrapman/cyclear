<?php

namespace Cyclear\GameBundle\Form\Filter;

class RennerFilterType extends \Symfony\Component\Form\AbstractType
{
   
    public function buildForm(\Symfony\Component\Form\FormBuilderInterface $builder, array $options)
    {
        $builder->add('naam', 'text', array('required' => false));
    }

    
    public function getName()
    {
        return 'renner_filter';
    }

    
}
