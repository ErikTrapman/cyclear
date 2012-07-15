<?php

namespace Cyclear\GameBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class WedstrijdType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('datum', 'date', array('format' => 'dd-MM-y'))
            ->add('naam')
            ->add('uitslagtype', 'entity', array('class' => 'CyclearGameBundle:UitslagType'))    
        ;
    }

    public function getName()
    {
        return 'cyclear_gamebundle_wedstrijdtype';
    }
}
