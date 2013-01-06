<?php

namespace Cyclear\GameBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class NieuwsType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('seizoen','seizoen_selector')
            ->add('titel')
            ->add('content', 'textarea', array('attr' => array('rows' => 32, 'cols' => 32)))
        ;
    }

    public function getName()
    {
        return 'cyclear_gamebundle_nieuwstype';
    }
}
