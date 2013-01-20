<?php

namespace Cyclear\GameBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
//use Cyclear\GameBundle\Validator\Constraints as CyclearAssert;

class UitslagNewType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            //->add('url', 'text', array('attr' => array('size' => 100), 'mapped' => false, 'required' => false))
            //->add('cq_wedstrijdid', 'text', array('mapped' => false, 'required' => false,'label'=>'CQ-id'))
            ->add('url', 'eriktrapman_cqrankingmatchselector_type', 
                array('mapped' => false, 'required' => true,'label'=>'CQ-wedstrijd'))
            ->add('datum', 'date', array('format' => 'dd-MM-y'))
            ->add('uitslagtype', 'entity', array('mapped' => false, 'class' => 'CyclearGameBundle:UitslagType'))
            ->add('seizoen', 'seizoen_selector', array('mapped' => false))
            ->add('refentiewedstrijd', 'entity', array('required' => false, 'mapped' => false, 'class' => 'CyclearGameBundle:Wedstrijd',
                'query_builder' => function( \Doctrine\ORM\EntityRepository $r ) {
                    return $r->createQueryBuilder('w')
                        ->add('orderBy', 'w.id DESC')
                        ->setMaxResults(30);
                }))
        ;
    }
    
    public function setDefaultOptions(\Symfony\Component\OptionsResolver\OptionsResolverInterface $resolver)
    {
        //$collection = new \Symfony\Component\Validator\Constraints\Collection();
        
        //$resolver->setDefaults(array('validation_constraint' => new \Cyclear\GameBundle\Validator\Constraints\UitslagNewValidator() ));
    }

    public function getName()
    {
        return 'cyclear_gamebundle_uitslagnewtype';
    }
}