<?php

namespace Cyclear\GameBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Cyclear\GameBundle\Validator\Constraints as CyclearAssert;

/**
 * @CyclearAssert\UserTransfer
 * 
 */
class TransferUserType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (null !== $options['renner_uit']) {
            $builder
                ->add('renner_in', 'renner_autocomplete', array('property_path' => false))
            ;
        }
        if (null !== $options['renner_in']) {
            $ploeg = $options['ploeg'];
            $builder
                ->add('renner_uit', 'entity', array(
                    'property_path' => false,
                    'class' => 'CyclearGameBundle:Renner',
                    'query_builder' => function(\Doctrine\ORM\EntityRepository $e) use ($ploeg) {
                        return $e->createQueryBuilder('r')->where('r.ploeg = :ploeg')->setParameter(':ploeg', $ploeg);
                    }))
            ;
        }
    }

    public function getName()
    {
        return 'cyclear_gamebundle_transferusertype';
    }

    public function setDefaultOptions(\Symfony\Component\OptionsResolver\OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array('renner_in' => null, 'renner_uit' => null, 'ploeg' => null)
        );
    }
}
