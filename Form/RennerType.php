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

class RennerType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('naam')
            ->add('cqranking_id', null, array('required' => true, 'label' => 'CQ-id'))
            ->add('country', 'entity', array(
                'class' => 'CyclearGameBundle:Country',
                'query_builder' => function(\Doctrine\ORM\EntityRepository $e) {
                    return $e->createQueryBuilder('c')->orderBy('c.name');
                }))
        ;
    }

    public function getName()
    {
        return 'cyclear_gamebundle_rennertype';
    }
}
