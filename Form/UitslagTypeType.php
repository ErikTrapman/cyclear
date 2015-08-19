<?php

/*
 * This file is part of the Cyclear-game package.
 *
 * (c) Erik Trapman <veggatron@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cyclear\GameBundle\Form;

class UitslagTypeType extends \Symfony\Component\Form\AbstractType
{

    public function buildForm(\Symfony\Component\Form\FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('naam')
            ->add('maxResults','integer')
            ->add('isGeneralClassification',null,array('required'=>false))
            ->add('cqParsingStrategy', 'eriktrapman_cqparser_strategy', array('property_path'=>'cqParsingStrategy'))
        ;
    }

    public function getName()
    {
        return 'cyclear_gamebundle_uitslagtypetype';
    }
}
