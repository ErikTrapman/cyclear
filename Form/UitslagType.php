<?php

namespace Cyclear\GameBundle\Form;

use Cyclear\GameBundle\Form\DataTransformer\RennerNameToRennerIdTransformer;
use Cyclear\GameBundle\Entity\RennerRepository;
use Symfony\Bundle\DoctrineBundle\Registry;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class UitslagType extends AbstractType {

    public function buildForm(FormBuilder $builder, array $options) {
        
        // array( 'allow_add' => true, 'type' => $w)
        $builder->add('positie')
                ->add('punten')
                ->add('wedstrijd','entity', array('class'=> 'Cyclear\GameBundle\Entity\Wedstrijd') )
                ->add('ploeg', null, array('required' => false))
                ->add('renner', 'renner_selector' );
                
    }

    public function getName() {
        return 'cyclear_gamebundle_uitslagtype';
    }

    public function getDefaultOptions(array $options) {
        return array('data_class' => 'Cyclear\GameBundle\Entity\Uitslag', 'registry' => null);
    }

}
