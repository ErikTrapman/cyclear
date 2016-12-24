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

use FOS\UserBundle\Form\Type\ProfileFormType as BaseType;
use Cyclear\GameBundle\Form\EventListener\IsAdminFieldSubscriber;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserEditType extends BaseType
{

    public function buildForm(\Symfony\Component\Form\FormBuilderInterface $builder, array $options)
    {
        $user = $options['user'];
        $this->buildUserForm($builder, $options);
        $builder
            ->add('ploeg', EntityType::class, array(
                'required' => false,
                'class' => 'CyclearGameBundle:Ploeg',
                'choice_label' => 'naamWithSeizoen',
                'query_builder' => function (\Doctrine\ORM\EntityRepository $e) use ($user) {
                    return $e->createQueryBuilder("p")
                        //->where("p.user IS NULL OR p.user = :user")
                        //->setParameter("user", $user)
                        ->orderBy("p.seizoen ASC, p.afkorting");
                },
                'multiple' => true,
                'expanded' => false
            ))
            ->add('enabled', null, array('required' => false));
        $subscriber = new IsAdminFieldSubscriber($builder->getFormFactory());
        $builder->addEventSubscriber($subscriber);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array('user' => null));
    }

    public function getName()
    {
        return 'admin_user_edit';
    }
}
