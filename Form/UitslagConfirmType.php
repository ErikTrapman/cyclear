<?php

namespace Cyclear\GameBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Cyclear\GameBundle\Form\UitslagType;

class UitslagConfirmType extends AbstractType {

    public function buildForm(FormBuilder $builder, array $options) {

        $builder->add('wedstrijd', new WedstrijdType())
                ->add('uitslag', 'collection', array('type' => new UitslagPrepareType(),
                    'allow_add' => true,
                    'by_reference' => false,
                    'options' => array('registry' => $options['data']['registry'])))
                ;
    }

    public function getName() {
        return 'cyclear_gamebundle_uitslagconfirmtype';
    }

}
