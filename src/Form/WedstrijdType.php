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

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WedstrijdType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $dateOptions = array('format' => 'dd-MM-y');
        if ($options['default_date']) {
            $dateOptions['data'] = $options['default_date'];
        }
        $builder
            ->add('datum', DateType::class, $dateOptions)
            ->add('naam')
            ->add('uitslagtype', EntityType::class, array('class' => \App\Entity\UitslagType::class, 'mapped' => false))
            ->add('seizoen', SeizoenSelectorType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\Entity\Wedstrijd',
            'default_date' => null
        ));
    }

    public function getName()
    {
        return 'cyclear_gamebundle_wedstrijdtype';
    }
}
