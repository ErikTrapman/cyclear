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
use Cyclear\GameBundle\Validator\Constraints as CyclearAssert;

/**
 * @CyclearAssert\UserTransfer
 *
 */
class TransferUserType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (null !== $options['renner_uit']) {
            $builder
                ->add('renner_in', 'renner_selector', array('label' => 'Renner in', 'mapped' => 'rennerIn'));
        }
        if (null !== $options['renner_in']) {
            $ploeg = $options['ploeg'];
            $ploegRenners = array_merge(array(0), $options['ploegRenners']);
            $builder
                ->add('renner_uit', 'entity', array(
                    'mapped' => 'rennerUit',
                    'label' => 'Renner uit',
                    'class' => 'CyclearGameBundle:Renner',
                    'query_builder' => function (\Doctrine\ORM\EntityRepository $e) use ($ploeg, $ploegRenners) {
                        return $e->createQueryBuilder('r')
                            ->where('r IN ( :renners )')
                            ->setParameter(':renners', $ploegRenners)
                            ->orderBy('r.naam');
                    }));
        }
        $builder->add('userComment', null, ['label' => 'Commentaar', 'attr' => ['placeholder' => 'Commentaar...']]);
    }

    public function getName()
    {
        return 'cyclear_gamebundle_transferusertype';
    }

    public function setDefaultOptions(\Symfony\Component\OptionsResolver\OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array('renner_in' => null, 'renner_uit' => null, 'ploeg' => null, 'ploegRenners' => array())
        );
    }
}
