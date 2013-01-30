<?php

namespace Cyclear\GameBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class WedstrijdType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('datum', 'date', array('format' => 'dd-MM-y','data'=>new \DateTime()))
            ->add('naam',null, array('required'=>false))
            ->add('uitslagtype', 'entity', array('class' => 'CyclearGameBundle:UitslagType'))
            ->add('seizoen', 'seizoen_selector')

        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Cyclear\GameBundle\Entity\Wedstrijd'
        ));
    }

    public function getName()
    {
        return 'cyclear_gamebundle_wedstrijdtype';
    }
}
