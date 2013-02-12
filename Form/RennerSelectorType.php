<?php

namespace Cyclear\GameBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class RennerSelectorType extends AbstractType
{
    /**
     *
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;
    
    private $rennerManager;

    public function __construct(\Doctrine\ORM\EntityManager $em, $rennerManager)
    {
        $this->em = $em;
        $this->rennerManager = $rennerManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $transformer = new DataTransformer\RennerNameToRennerIdTransformer($this->em, $this->rennerManager);
        //$builder->add('renner','text');
        $builder->appendClientTransformer($transformer);
    }

    public function setDefaultOptions(\Symfony\Component\OptionsResolver\OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array('invalid_message' => 'De ingevulde renner is niet teruggevonden'));
    }

    public function getParent()
    {
        return 'text';
    }

    public function getName()
    {
        return 'renner_selector';
    }
}