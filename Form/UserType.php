<?php

namespace Cyclear\GameBundle\Form;

use Symfony\Component\Form\AbstractType;
use FOS\UserBundle\Form\Type\RegistrationFormType as BaseType;
use Symfony\Component\Form\FormBuilderInterface;

class UserType extends BaseType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('ploeg', 'entity', array(
                'required' => false,
                'class' => 'CyclearGameBundle:Ploeg',
                'query_builder' => function(\Doctrine\ORM\EntityRepository $e){
                    return $e->createQueryBuilder("p")->where("p.user IS NULL");
                }
                ))
        ;
    }

    public function getName()
    {
        return 'admin_user_new';
    }
}
?>
