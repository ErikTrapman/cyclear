<?php

namespace Cyclear\GameBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class UitslagNewType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('datum', 'date', array('format' => 'dd-MM-y'))
            ->add('url', 'text', array('attr' => array('size' => 100)))
            ->add('uitslagtype', 'cyclear_cqparser_strategy')
            ->add('refentiewedstrijd', 'entity', array('required' => false, 'class' => 'CyclearGameBundle:Wedstrijd',
                'query_builder' => function( \Doctrine\ORM\EntityRepository $r ) {
                    return $r->createQueryBuilder('w')
                        ->add('orderBy', 'w.id DESC')
                        ->setMaxResults(30);
                }))
        ;
    }

    public function getName()
    {
        return 'cyclear_gamebundle_uitslagnewtype';
    }
}