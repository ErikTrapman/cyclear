<?php declare(strict_types=1);

namespace App\Form\Extension;

use App\Form\EventListener\IsAdminFieldSubscriber;
use FOS\UserBundle\Form\Type\ProfileFormType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProfileFormTypeExtension extends AbstractTypeExtension
{
    public static function getExtendedTypes(): iterable
    {
        return [ProfileFormType::class];
    }

    public function buildForm(\Symfony\Component\Form\FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('enabled', null, ['required' => false])
            ->add('firstName', null, ['required' => false]);
        $subscriber = new IsAdminFieldSubscriber($builder->getFormFactory());
        $builder->addEventSubscriber($subscriber);
        $builder->remove('current_password');
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['user' => null]);
    }
}
