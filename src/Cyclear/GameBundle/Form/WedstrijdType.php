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
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class WedstrijdType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $dateOptions = array('format' => 'dd-MM-y');
        if ($options['default_date']) {
            $dateOptions['data'] = $options['default_date'];
        }
        $builder
            ->add('datum', 'date', $dateOptions)
            ->add('naam')
            ->add('uitslagtype', 'entity', array('class' => 'CyclearGameBundle:UitslagType', 'mapped' => false))
            ->add('seizoen', 'seizoen_selector');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Cyclear\GameBundle\Entity\Wedstrijd',
            'default_date' => null
        ));
    }

    public function getName()
    {
        return 'cyclear_gamebundle_wedstrijdtype';
    }
}
