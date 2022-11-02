<?php declare(strict_types=1);

namespace App\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WedstrijdType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $dateOptions = ['format' => 'dd-MM-y'];
        if ($options['default_date']) {
            $dateOptions['data'] = $options['default_date'];
        }
        $builder
            ->add('datum', DateType::class, $dateOptions)
            ->add('naam')
            ->add('uitslagtype', EntityType::class, ['class' => \App\Entity\UitslagType::class, 'mapped' => false])
            ->add('seizoen', SeizoenSelectorType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\Wedstrijd',
            'default_date' => null,
        ]);
    }

    public function getName()
    {
        return 'cyclear_gamebundle_wedstrijdtype';
    }
}
