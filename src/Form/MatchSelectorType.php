<?php declare(strict_types=1);

namespace App\Form;

use App\CQRanking\Parser\Match\MatchParser;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MatchSelectorType extends AbstractType
{
    /**
     * @var MatchParser
     */
    private $matchParser;

    public function __construct(MatchParser $matchParser)
    {
        $this->matchParser = $matchParser;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $options = [];
        $options['empty_value'] = 'Maak keuze';
        $options['choices'] = array_flip($this->matchParser->getMatches());
        $resolver->setDefaults($options);
    }

    public function getName()
    {
        return 'eriktrapman_cqrankingmatchselector_type';
    }

    public function getBlockPrefix()
    {
        return $this->getName();
    }

    public function getParent()
    {
        return ChoiceType::class;
    }
}
