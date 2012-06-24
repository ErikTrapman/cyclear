<?php

namespace Cyclear\GameBundle\Form;

use Symfony\Component\Form\AbstractType;
use FOS\UserBundle\Form\Type\RegistrationFormType as BaseType;
use Symfony\Component\Form\FormBuilder;

class UserType extends BaseType {

    public function buildForm(FormBuilder $builder, array $options) {
        parent::buildForm($builder, $options);

        $builder
                ->add('ploeg', null, array('required' => false))
        ;
    }

    public function getName() {
        return 'cyclear_gamebundle_usertype';
    }

}

?>
