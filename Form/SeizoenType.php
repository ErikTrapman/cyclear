<?php

namespace Cyclear\GameBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class SeizoenType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('identifier', 'text')
                ->add('current', null, array('required' => false))
                ->add('closed', null, array('required' => false))
        ;
    }

    public function getName() {
        return 'cyclear_gamebundle_seizoentype';
    }

}
