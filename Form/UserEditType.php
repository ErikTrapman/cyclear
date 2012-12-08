<?php

namespace Cyclear\GameBundle\Form;

use FOS\UserBundle\Form\Type\ProfileFormType as BaseType;

class UserEditType extends BaseType
{
    
    public function buildForm(\Symfony\Component\Form\FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder
            ->add('ploeg', 'entity', array(
                'required' => false,
                'class' => 'CyclearGameBundle:Ploeg',
                'multiple' => true
                ))
        ;
    }

    
    public function getName()
    {
        return 'admin_user_edit';
    }
    
}
