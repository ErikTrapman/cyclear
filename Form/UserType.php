<?php

namespace Cyclear\GameBundle\Form;

use Symfony\Component\Form\AbstractType;
use FOS\UserBundle\Form\Type\RegistrationFormType as BaseType;
use Symfony\Component\Form\FormBuilderInterface;

class UserType extends BaseType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        parent::buildForm($builder, $options);

        $builder
                ->add('ploeg', null, array('required' => false))
        ;
    }

    public function getName() {
        return 'admin_user_new';
    }

}

?>
