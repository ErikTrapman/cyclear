<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cyclear\GameBundle\Form\Filter;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class PloegFilterType extends AbstractType {

    private $em;

    public function __construct(\Doctrine\ORM\EntityManager $em) {
        $this->em = $em;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('naam', 'text', array('required' => false))
                ->add('afkorting', 'text', array('required' => false));
        $builder->add('user', 'entity', array('class' => 'Cyclear\GameBundle\Entity\User', 'required' => false));
    }

    public function getName() {
        return 'ploeg_filter';
    }

}

?>
