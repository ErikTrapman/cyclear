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
            ->add('url', 'text', array('attr' => array('size' => 100), 'mapped' => false, 'required' => false))
            ->add('cq_wedstrijd-id', 'text', array('mapped' => false, 'required' => false))
            ->add('uitslagtype', 'entity', array('mapped' => false, 'class' => 'CyclearGameBundle:UitslagType'))
            ->add('seizoen', 'entity', array(
                'mapped' => false,
                'class' => 'CyclearGameBundle:Seizoen',
                'query_builder' => function(\Doctrine\ORM\EntityRepository $e) {
                    return $e->createQueryBuilder('s'); //->where('s.current = 1');
                }))
            ->add('refentiewedstrijd', 'entity', array('required' => false, 'mapped' => false, 'class' => 'CyclearGameBundle:Wedstrijd',
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