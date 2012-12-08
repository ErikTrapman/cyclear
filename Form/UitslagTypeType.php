<?php

namespace Cyclear\GameBundle\Form;

class UitslagTypeType extends \Symfony\Component\Form\AbstractType
{

    public function buildForm(\Symfony\Component\Form\FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('naam')
            ->add('maxResults','integer')
            ->add('isGeneralClassification',null,array('required'=>false))
            ->add('cqParsingStrategy', 'cyclear_cqparser_strategy', array('property_path'=>'cqParsingStrategy'))
        ;
    }

    public function getName()
    {
        return 'cyclear_gamebundle_uitslagtypetype';
    }
}
