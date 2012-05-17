<?php

namespace Cyclear\GameBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class TransferType extends AbstractType {

    public function buildForm(FormBuilder $builder, array $options) {
        // 
        // , 'jquery_entity_combobox', array('required'=>true, 'class' => 'Cyclear\GameBundle\Entity\Ploeg')
        $builder
                //->add('renner','jquery_entity_combobox', array('class' => 'Cyclear\GameBundle\Entity\Renner') )
                ->add('renner', 'renner_selector')
                ->add('ploegNaar', null, array('required' => true))
                ->add('datum', 'jquery_date', array('format' => 'dd-MM-y'))
        ;
    }

    public function getName() {
        return 'cyclear_gamebundle_transfertype';
    }

}
