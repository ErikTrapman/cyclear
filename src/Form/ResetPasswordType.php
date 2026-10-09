<?php declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ResetPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('plainPassword', RepeatedType::class, [
            'type' => PasswordType::class,
            'options' => ['attr' => ['autocomplete' => 'new-password']],
            'first_options' => ['label' => 'Nieuw wachtwoord'],
            'second_options' => ['label' => 'Wachtwoord controle'],
            'invalid_message' => 'De wachtwoorden komen niet overeen.',
            'constraints' => [new NotBlank(), new Length(min: 2, max: 4096)],
        ]);
    }
}
