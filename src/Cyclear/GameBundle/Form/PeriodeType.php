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

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class PeriodeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('start','date')
            ->add('eind','date')
            ->add('transfers',null, array('label'=>'Aantal transfers'))
            ->add('seizoen','seizoen_selector')
        ;
    }

    public function getName()
    {
        return 'cyclear_gamebundle_periodetype';
    }
}
