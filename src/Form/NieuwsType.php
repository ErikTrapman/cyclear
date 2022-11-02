<?php declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;

class NieuwsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('seizoen', SeizoenSelectorType::class)
            ->add('titel')
            ->add('content', TextareaType::class, ['attr' => ['rows' => 32, 'cols' => 32]]);
    }

    public function getName()
    {
        return 'cyclear_gamebundle_nieuwstype';
    }
}
