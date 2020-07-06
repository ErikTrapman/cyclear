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
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;

class NieuwsType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('seizoen', SeizoenSelectorType::class)
            ->add('titel')
            ->add('content', TextareaType::class, array('attr' => array('rows' => 32, 'cols' => 32)));
    }

    public function getName()
    {
        return 'cyclear_gamebundle_nieuwstype';
    }
}
