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
            ->add('datum', 'date', array('format' => 'dd-MM-y'))
            ->add('naam')
            ->add('uitslagtype', 'entity', array('class' => 'CyclearGameBundle:UitslagType'))
            ->add('seizoen', 'entity', array(
                'class' => 'CyclearGameBundle:Seizoen',
                'query_builder' => function(\Doctrine\ORM\EntityRepository $e) {
                    return $e->createQueryBuilder('s'); //->where('s.current = 1');
                }))

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
