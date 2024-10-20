<?php declare(strict_types=1);

namespace App\Form\Extension;

use FOS\UserBundle\Form\Type\RegistrationFormType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;

class RegistrationFormTypeExtension extends AbstractTypeExtension
{
    public static function getExtendedTypes(): iterable
    {
        return [RegistrationFormType::class];
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->remove('plainPassword');
        $builder->add('firstName', null, ['required' => false]);
    }
}
