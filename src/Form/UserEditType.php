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

use FOS\UserBundle\Form\Type\ProfileFormType as BaseType;
use App\Form\EventListener\IsAdminFieldSubscriber;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserEditType extends BaseType
{
    public function __construct($class)
    {
        parent::__construct($class);
    }

    public function buildForm(\Symfony\Component\Form\FormBuilderInterface $builder, array $options)
    {
        $this->buildUserForm($builder, $options);
        $builder
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
