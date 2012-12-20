<?php

namespace Cyclear\GameBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Cyclear\GameBundle\Form\UitslagType;

class UitslagConfirmType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('wedstrijd', new WedstrijdType())
            ->add('uitslag', 'collection', array('type' => new UitslagPrepareType(),
                'allow_add' => true,
                'by_reference' => false,
                'options' => array('registry' => $options['data']['registry'])))
            ->add('seizoen', 'entity', array(
                'class' => 'CyclearGameBundle:Seizoen',
                'query_builder' => function(\Doctrine\ORM\EntityRepository $e) {
                    return $e->createQueryBuilder('s'); //->where('s.current = 1');
                }))
        ;
    }

    public function getName()
    {
        return 'cyclear_gamebundle_uitslagconfirmtype';
    }
}
