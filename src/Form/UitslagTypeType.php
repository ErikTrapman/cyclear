<?php declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class UitslagTypeType extends \Symfony\Component\Form\AbstractType
{
    public function buildForm(\Symfony\Component\Form\FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('naam')
            ->add('maxResults', IntegerType::class)
            ->add('isGeneralClassification', null, ['required' => false])
            ->add('cqParsingStrategy', StrategySelectorType::class, ['property_path' => 'cqParsingStrategy'])
            ->add('automaticResolvingCategories');
    }

    public function getName()
    {
        return 'cyclear_gamebundle_uitslagtypetype';
    }
}
