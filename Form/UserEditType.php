<?php

namespace Cyclear\GameBundle\Form;

use FOS\UserBundle\Form\Type\ProfileFormType as BaseType;
use Cyclear\GameBundle\Form\EventListener\IsAdminFieldSubscriber;

class UserEditType extends BaseType
{

    public function buildForm(\Symfony\Component\Form\FormBuilderInterface $builder, array $options)
    {
        $user = $options['user'];
        $this->buildUserForm($builder, $options);
        $builder
            ->add('ploeg', 'entity', array(
                'required' => false,
                'class' => 'CyclearGameBundle:Ploeg',
                'property' => 'naamWithSeizoen',
                'query_builder' => function(\Doctrine\ORM\EntityRepository $e ) use($user) {
                    return $e->createQueryBuilder("p")
                        //->where("p.user IS NULL OR p.user = :user")
                        //->setParameter("user", $user)
                        ->orderBy("p.seizoen ASC, p.naam")
                    ;
                },
                'multiple' => true,
                'expanded' => false
            ))
                    ->add('enabled', null, array('required'=>false))
        ;
        $subscriber = new IsAdminFieldSubscriber($builder->getFormFactory());
        $builder->addEventSubscriber($subscriber);
    }

    public function setDefaultOptions(\Symfony\Component\OptionsResolver\OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array('user' => null));
    }

    public function getName()
    {
        return 'admin_user_edit';
    }
}
