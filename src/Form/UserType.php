<?php declare(strict_types=1);

namespace App\Form;

use FOS\UserBundle\Form\Type\RegistrationFormType as BaseType;
use Symfony\Component\Form\FormBuilderInterface;

class UserType extends BaseType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder->remove('plainPassword');
    }

    public function getName()
    {
        return 'admin_user_new';
    }
}
