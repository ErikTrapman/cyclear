<?php

namespace Cyclear\GameBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UitslagType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {

        // array( 'allow_add' => true, 'type' => $w)
        $builder->add('positie')
                ->add('ploegPunten')
                ->add('wedstrijd', 'entity', array('class' => 'Cyclear\GameBundle\Entity\Wedstrijd'))
                ->add('ploeg', null, array('required' => false))
                ->add('renner', 'renner_selector');
    }

    public function getName() {
        return 'cyclear_gamebundle_uitslagtype';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'Cyclear\GameBundle\Entity\Uitslag', 'registry' => null
        ));
    }

}
