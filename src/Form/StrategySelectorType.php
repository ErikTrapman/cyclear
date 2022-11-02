<?php declare(strict_types=1);

namespace App\Form;

use App\Form\DataTransformer\ConstantToStrategyClassTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StrategySelectorType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer(new ConstantToStrategyClassTransformer());
    }

    public function getBlockPrefix()
    {
        return $this->getName();
    }

    public function getName()
    {
        return 'eriktrapman_cqparser_strategy';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'empty_value' => 'Maak keuze',
            'choices' => array_flip([
                'App\CQRanking\Parser\Strategy\Y2012\OneDay' => 'Eendagskoers 2012',
                'App\CQRanking\Parser\Strategy\Y2012\Stage' => 'Etappe 2012',
                'App\CQRanking\Parser\Strategy\Y2012\GeneralClassification' => 'Alg. klassement 2012',
                'App\CQRanking\Parser\Strategy\Y2013\OneDay' => 'Eendagskoers 2013',
                'App\CQRanking\Parser\Strategy\Y2013\Stage' => 'Etappe 2013',
                'App\CQRanking\Parser\Strategy\Y2013\GeneralClassification' => 'Alg. klassement 2013',
            ]),
        ]);
    }

    public function getParent()
    {
        return ChoiceType::class;
    }
}
