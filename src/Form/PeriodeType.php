<?php declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;

class PeriodeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('start', DateType::class)
            ->add('eind', DateType::class)
            ->add('transfers', null, ['label' => 'Aantal transfers'])
            ->add('seizoen', SeizoenSelectorType::class);
    }

    public function getName()
    {
        return 'cyclear_gamebundle_periodetype';
    }
}
