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
use Symfony\Component\Validator\Constraints\NotNull;

class PloegType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('naam')
            ->add('afkorting')
            ->add('seizoen', SeizoenSelectorType::class)
            ->add('user', null, [
                'required' => true, 'constraints' => [new NotNull()],
                'query_builder' => function (EntityRepository $e) {
                    return $e->createQueryBuilder('u')->orderBy('u.email', 'ASC');
                }
            ]);
    }

    public function getName()
    {
        return 'cyclear_gamebundle_ploegtype';
    }
}
