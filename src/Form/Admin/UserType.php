<?php declare(strict_types=1);

namespace App\Form\Admin;

use App\Entity\User;
use App\Form\EventListener\IsAdminFieldSubscriber;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Admin form for users. On creation only the account details are asked; editing also manages enabled and admin rights.
 */
class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', null, ['label' => 'Gebruikersnaam'])
            ->add('email', EmailType::class, ['label' => 'E-mail']);
        if ($options['edit']) {
            $builder->add('enabled', null, ['required' => false]);
        }
        $builder->add('firstName', null, ['required' => false]);
        if ($options['edit']) {
            $builder->addEventSubscriber(new IsAdminFieldSubscriber($builder->getFormFactory()));
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'edit' => false,
        ]);
        $resolver->setAllowedTypes('edit', 'bool');
    }
}
