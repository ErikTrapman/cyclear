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
        $seizoen = $options['seizoen'];
        $builder
            ->add('renner', 'renner_selector')
            ->add('ploegNaar', null, array('required' => true, 'query_builder' => function($e) use ($seizoen) {
                    return $e->createQueryBuilder('p')->where('p.seizoen = :seizoen')->setParameter('seizoen', $seizoen);
                }))
            ->add('datum', 'date', array('format' => 'dd-MM-y'))
            ->add('transferType', 'choice', array('choice_list' => $choicelist))
            ->add('seizoen', 'seizoen_selector')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'admin' => true,
                'seizoen' => null)
        );
    }

    public function getName()
    {
        return 'cyclear_gamebundle_transfertype';
    }
}
