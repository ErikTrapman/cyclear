<?php

namespace Cyclear\GameBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class PeriodeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('start','date', array('format' => 'dd-MM-y', 'changeMonth' => true, 'changeYear' => true))
            ->add('eind','date', array('format' => 'dd-MM-y', 'changeMonth'=>true, 'changeYear' => true))
            ->add('transfers',null, array('label'=>'Aantal transfers'))
        ;
    }

    public function getName()
    {
        return 'cyclear_gamebundle_periodetype';
    }
}
