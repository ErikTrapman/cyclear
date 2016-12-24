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

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class RennerType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('naam')
            ->add('cqranking_id', null, array('required' => true, 'label' => 'CQ-id'))
            ->add('country', EntityType::class, array(
                'class' => 'CyclearGameBundle:Country',
                'query_builder' => function (\Doctrine\ORM\EntityRepository $e) {
                    return $e->createQueryBuilder('c')->orderBy('c.name');
                }))
            ->add('twitter', null, ['required' => false]);
    }

    public function getName()
    {
        return 'cyclear_gamebundle_rennertype';
    }
}
