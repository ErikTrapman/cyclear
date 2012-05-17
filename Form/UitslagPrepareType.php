<?php

namespace Cyclear\GameBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class UitslagPrepareType extends AbstractType {

    public function buildForm(FormBuilder $builder, array $options) {
        // array( 'allow_add' => true, 'type' => $w)
        $builder->add('positie')
                ->add('punten')
                ->add('ploeg', null, array('required' => false))
                ->add('renner', 'renner_selector' );
    }

    public function getName() {
        return 'cyclear_gamebundle_uitslagpreparetype';
    }
    
    
    public function getDefaultOptions(array $options) {
        return array('registry' => null);
    }
    
}

?>
