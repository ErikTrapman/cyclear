<?php

namespace Cyclear\GameBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class RennerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('naam')
            ->add('ploeg', 'entity', array('required'=>false, 'class' => 'Cyclear\GameBundle\Entity\Ploeg'))
            ->add('cqranking_id')
        ;
    }

    public function getName()
    {
        return 'cyclear_gamebundle_rennertype';
    }
}
