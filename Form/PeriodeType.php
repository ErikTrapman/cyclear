<?php

namespace Cyclear\GameBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class PeriodeType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('start','jquery_date', array('format' => 'dd-MM-y', 'changeMonth' => true, 'changeYear' => true))
            ->add('eind','jquery_date', array('format' => 'dd-MM-y', 'changeMonth'=>true, 'changeYear' => true))
            ->add('transfers',null, array('label'=>'Aantal transfers'))
        ;
    }

    public function getName()
    {
        return 'cyclear_gamebundle_periodetype';
    }
}
