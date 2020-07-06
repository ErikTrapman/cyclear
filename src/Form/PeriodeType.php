<?php

/*
 * This file is part of the Cyclear-game package.
 *
 * (c) Erik Trapman <veggatron@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;

class PeriodeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('start', DateType::class)
            ->add('eind', DateType::class)
            ->add('transfers', null, array('label' => 'Aantal transfers'))
            ->add('seizoen', SeizoenSelectorType::class);
    }

    public function getName()
    {
        return 'cyclear_gamebundle_periodetype';
    }
}
