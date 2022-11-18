<?php declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;

class SpelregelsType extends AbstractType
{
    /**
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('seizoen', SeizoenSelectorType::class)
            ->add('content', TextareaType::class, ['attr' => ['rows' => 32, 'cols' => 32]]);
    }
}
