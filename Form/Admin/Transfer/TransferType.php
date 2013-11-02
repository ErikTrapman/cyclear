<?php

/*
 * This file is part of the Cyclear-game package.
 *
 * (c) Erik Trapman <veggatron@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cyclear\GameBundle\Form\Admin\Transfer;

use Cyclear\GameBundle\Entity\Transfer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TransferType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $seizoen = $options['seizoen'];

        switch ($options['transfertype']) {
            case Transfer::DRAFTTRANSFER:
                $builder
                    ->add('renner', 'renner_selector')
                    ->add('ploegNaar', null, array('label' => 'Ploeg naar', 'required' => true, 'query_builder' => function($e) use ($seizoen) {
                            return $e->createQueryBuilder('p')->where('p.seizoen = :seizoen')->setParameter('seizoen', $seizoen);
                        }));
                break;
            case Transfer::ADMINTRANSFER:
                $builder
                    ->add('renner', 'renner_selector', array('mapped' => false))
                    ->add('renner2', 'renner_selector', array('mapped' => false, 'label' => 'Renner'));
                break;
        }

        $builder
            ->add('datum', 'date', array('format' => 'dd-MM-y'))
            ->add('seizoen', 'seizoen_selector')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'admin' => true,
                'seizoen' => null,
                'transfertype' => Transfer::DRAFTTRANSFER)
        );
    }

    public function getName()
    {
        return 'cyclear_gamebundle_transfertype';
    }
}
