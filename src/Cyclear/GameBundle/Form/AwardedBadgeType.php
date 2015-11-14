<?php

namespace Cyclear\GameBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AwardedBadgeType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('badge')
            ->add('user', null, ['query_builder' => function (EntityRepository $e) {
                return $e->createQueryBuilder('u')->orderBy('u.username');
            }])
            ->add('recurringAmount');
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Cyclear\GameBundle\Entity\AwardedBadge'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'cyclear_gamebundle_awardedbadge';
    }
}
