<?php

/*
 * This file is part of the Cyclear-game package.
 *
 * (c) Erik Trapman <veggatron@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cyclear\GameBundle\Form\Admin\Transfer;

use Cyclear\GameBundle\Entity\Transfer;
use Cyclear\GameBundle\Form\RennerSelectorType;
use Cyclear\GameBundle\Form\SeizoenSelectorType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

class TransferEditType extends \Symfony\Component\Form\AbstractType
{

    public function getName()
    {
        return 'cyclear_gamebundle_transferedittype';
    }

    public function buildForm(\Symfony\Component\Form\FormBuilderInterface $builder, array $options)
    {
        $choices = array(
            Transfer::DRAFTTRANSFER => 'draft transfer',
            Transfer::ADMINTRANSFER => 'admin transfer',
            Transfer::USERTRANSFER => 'user transfer');
        $builder
            ->add('renner', RennerSelectorType::class, array('attr' => array('read_only' => true)))
            ->add('transferType', ChoiceType::class, array('choices' => array_flip($choices)))
            ->add('datum', DateTimeType::class)
            ->add('seizoen', SeizoenSelectorType::class);
    }
}