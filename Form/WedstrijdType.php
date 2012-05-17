<?php

namespace Cyclear\GameBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class WedstrijdType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('datum', 'jquery_date', array('format' => 'dd-MM-y'))
            ->add('naam')
            ->add('uitslagtype', 'entity', array('class' => 'CyclearGameBundle:UitslagType'))    
        ;
    }

    public function getName()
    {
        return 'cyclear_gamebundle_wedstrijdtype';
    }
}
