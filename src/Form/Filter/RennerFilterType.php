<?php declare(strict_types=1);

namespace App\Form\Filter;

use Symfony\Component\Form\Extension\Core\Type\TextType;

class RennerFilterType extends \Symfony\Component\Form\AbstractType
{
    public function buildForm(\Symfony\Component\Form\FormBuilderInterface $builder, array $options)
    {
        $builder->add('naam', TextType::class, [
            'required' => false,
            'label' => 'Naam / CQ-id', ]);
    }
}
