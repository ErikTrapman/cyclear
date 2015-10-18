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

class SeizoenType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('identifier', 'text')
            ->add('current', null, array('required' => false))
            ->add('closed', null, array('required' => false))
            ->add('start')
            ->add('end');
    }

    public function getName()
    {
        return 'cyclear_gamebundle_seizoentype';
    }

}
