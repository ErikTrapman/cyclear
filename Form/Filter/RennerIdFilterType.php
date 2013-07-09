<?php

namespace Cyclear\GameBundle\Form\Filter;

class RennerIdFilterType extends \Symfony\Component\Form\AbstractType
{

    public function buildForm(\Symfony\Component\Form\FormBuilderInterface $builder, array $options)
    {
        $builder->add('renner', 'renner_selector', array(
            'required' => false,
            'label' => 'Naam / CQ-id'));
    }

    public function getName()
    {
        return 'renner_id_filter';
    }
}
