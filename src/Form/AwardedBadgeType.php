<?php declare(strict_types=1);

namespace App\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AwardedBadgeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('badge')
            ->add('user', null, ['query_builder' => function (EntityRepository $e) {
                return $e->createQueryBuilder('u')->orderBy('u.username');
            }])
            ->add('recurringAmount');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\AwardedBadge',
        ]);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'cyclear_gamebundle_awardedbadge';
    }
}
