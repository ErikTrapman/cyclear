<?php declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class SeizoenType extends AbstractType
{
    /**
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('identifier', TextType::class)
            ->add('current', null, ['required' => false])
            ->add('closed', null, ['required' => false])
            ->add('start')
            ->add('end')
            ->add('maxPointsPerRider');
    }
}
