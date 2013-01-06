<?php

namespace Cyclear\GameBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class PeriodeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('start','date')
            ->add('eind','date')
            ->add('transfers',null, array('label'=>'Aantal transfers'))
            ->add('seizoen','seizoen_selector')
        ;
    }

    public function getName()
    {
        return 'cyclear_gamebundle_periodetype';
    }
}
