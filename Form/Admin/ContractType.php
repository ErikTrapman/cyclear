<?php

namespace Cyclear\GameBundle\Form\Admin;

class ContractType extends \Symfony\Component\Form\AbstractType
{

    public function getName()
    {
        return 'admin_contract';
    }

    public function buildForm(\Symfony\Component\Form\FormBuilderInterface $builder, array $options)
    {
        $seizoen = $options['seizoen'];
        $timeoptions = array('widget' => 'single_text', 'format' => 'd-M-yyyy k:m');
        $builder
            ->add('ploeg')
            ->add('renner', 'renner_selector')
            ->add('ploeg', null, array('label' => 'Ploeg naar', 'required' => true, 'query_builder' => function($e) use ($seizoen) {
                    return $e->createQueryBuilder('p')->where('p.seizoen = :seizoen')->setParameter('seizoen', $seizoen);
                }))
            ->add('start', null, $timeoptions)
            ->add('eind', null, $timeoptions)
            ->add('seizoen')
        ;
    }

    public function setDefaultOptions(\Symfony\Component\OptionsResolver\OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'seizoen' => null,
        ));
    }
}