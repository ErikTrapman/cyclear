<?php

namespace Cyclear\GameBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class PloegType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('naam')
            ->add('afkorting')
            ->add('user', 'jquery_entity_combobox', array('class'=>'Cyclear\UserBundle\Entity\User'));
        ;
    }

    public function getName()
    {
        return 'cyclear_gamebundle_ploegtype';
    }
}
