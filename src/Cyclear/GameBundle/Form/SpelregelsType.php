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

class SpelregelsType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('seizoen','seizoen_selector')
            ->add('content', 'textarea', array('attr' => array('rows' => 32, 'cols' => 32)))
        ;
    }

    public function getName()
    {
        return 'cyclear_gamebundle_spelregelstype';
    }
}
