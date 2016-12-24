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

use ErikTrapman\Bundle\CQRankingParserBundle\Form\Type\StrategySelectorType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class UitslagTypeType extends \Symfony\Component\Form\AbstractType
{

    public function buildForm(\Symfony\Component\Form\FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('naam')
            ->add('maxResults', IntegerType::class)
            ->add('isGeneralClassification', null, array('required' => false))
            ->add('cqParsingStrategy', StrategySelectorType::class, array('property_path' => 'cqParsingStrategy'))
            ->add('automaticResolvingCategories');
    }

    public function getName()
    {
        return 'cyclear_gamebundle_uitslagtypetype';
    }
}
