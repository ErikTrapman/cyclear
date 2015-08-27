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

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UitslagType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $seizoen = $options['seizoen'];
        // array( 'allow_add' => true, 'type' => $w)
        $builder->add('positie')
            ->add('ploegPunten')
            ->add('rennerPunten');
        if ($options['use_wedstrijd']) {
            $builder->add('wedstrijd', 'entity', array('class' => 'Cyclear\GameBundle\Entity\Wedstrijd', 'query_builder' =>
                function(EntityRepository $e) use ($seizoen) {
                    return $e->createQueryBuilder('w')->where('w.seizoen = :seizoen')->setParameter('seizoen', $seizoen)->orderBy('w.id', 'DESC');
                }));
        }
        $builder->add('ploeg', 'entity', array('required' => false,
                'class' => 'CyclearGameBundle:Ploeg',
                'query_builder' => function(EntityRepository $e) use ($seizoen) {
                    return $e->createQueryBuilder('p')->where('p.seizoen = :seizoen')->setParameter('seizoen', $seizoen)->orderBy('p.naam');
                }))
            ->add('renner', 'renner_selector');
    }

    public function getName()
    {
        return 'cyclear_gamebundle_uitslagtype';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Cyclear\GameBundle\Entity\Uitslag',
            'registry' => null,
            'seizoen' => null,
            'use_wedstrijd' => true
        ));
    }
}