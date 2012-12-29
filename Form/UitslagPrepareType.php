<?php

namespace Cyclear\GameBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UitslagPrepareType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // array( 'allow_add' => true, 'type' => $w)
        $builder
            ->add('positie', null, array('attr' => array('size' => 6)))
            ->add('renner', 'renner_selector')
            ->add('ploeg', null, array('required' => false))
            ->add('ploegPunten', null, array('attr' => array('size' => 6)))
            ->add('rennerPunten', null, array('attr' => array('size' => 6)))

        ;
    }

    public function getName()
    {
        return 'cyclear_gamebundle_uitslagpreparetype';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Cyclear\GameBundle\Entity\Uitslag', 'registry' => null
        ));
    }
}
?>
