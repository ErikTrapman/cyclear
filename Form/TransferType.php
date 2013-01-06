<?php

namespace Cyclear\GameBundle\Form;

use Cyclear\GameBundle\Entity\Transfer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceList;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TransferType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $choices = array(Transfer::DRAFTTRANSFER, Transfer::ADMINTRANSFER, Transfer::USERTRANSFER);
        $choicelist = new ChoiceList($choices, array('draft transfer', 'admin transfer', 'user transfer'));
        $builder
            //->add('renner', 'renner_selector')
            ->add('renner', 'renner_autocomplete',array('label'=>'Renner'))
            ->add('ploegNaar', null, array('required' => true))
            ->add('datum', 'date', array('format' => 'dd-MM-y'))
            ->add('transferType', 'choice', array('choice_list' => $choicelist))
            ->add('seizoen', 'seizoen_selector')
        ;
    }

    public function setOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array('admin' => true));
    }

    //FIXME samenvoegen van transferusertype met options->admin = true/false

    public function getName()
    {
        return 'cyclear_gamebundle_transfertype';
    }
}
