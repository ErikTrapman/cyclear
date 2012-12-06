<?php

namespace Cyclear\GameBundle\Form;

use Doctrine\ORM\EntityRepository;

class RennerAutocompleteType extends \Symfony\Component\Form\AbstractType
{

    public function getParent()
    {
        return 'autocomplete';
    }

    public function getName()
    {
        return 'renner_autocomplete';
    }

    public function setDefaultOptions(\Symfony\Component\OptionsResolver\OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'invalid_message' => 'De ingevulde renner is niet teruggevonden',
                'class' => 'CyclearGameBundle:Renner',
                'template' => 'CyclearGameBundle:Renner:autocomplete.html.twig',
                'search_fields' => array('s.naam', 's.cqranking_id'),
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('s');
                }
            )
        );
    }
}
