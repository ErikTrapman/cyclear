<?php

namespace Cyclear\GameBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UitslagType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {

        $seizoen = $options['seizoen'];

        // array( 'allow_add' => true, 'type' => $w)
        $builder->add('positie')
                ->add('ploegPunten')
                ->add('wedstrijd', 'entity', array('class' => 'Cyclear\GameBundle\Entity\Wedstrijd'))
                ->add('ploeg', 'entity', array('required' => false,
                        'class' => 'CyclearGameBundle:Ploeg',
                        'query_builder' => function(\Doctrine\ORM\EntityRepository $e) use ($seizoen) {
                            return $e->createQueryBuilder('p')->where('p.seizoen = :seizoen')->setParameter('seizoen', $seizoen)->orderBy('p.naam');
                        }))
                ->add('renner', 'renner_selector');
    }

    public function getName() {
        return 'cyclear_gamebundle_uitslagtype';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'Cyclear\GameBundle\Entity\Uitslag', 
            'registry' => null,
            'seizoen' => null
        ));
    }

}
