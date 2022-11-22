<?php declare(strict_types=1);

namespace App\Form\Filter;

use App\Form\RennerSelectorType;

class RennerIdFilterType extends \Symfony\Component\Form\AbstractType
{
    public function buildForm(\Symfony\Component\Form\FormBuilderInterface $builder, array $options)
    {
        $builder->add('renner', RennerSelectorType::class, [
            'required' => false,
            'label' => 'Naam / CQ-id', ]);
    }
}
