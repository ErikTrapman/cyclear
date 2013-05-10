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
    
    private $router;

    public function __construct(\Doctrine\ORM\EntityManager $em, $rennerManager, $router)
    {
        $this->em = $em;
        $this->rennerManager = $rennerManager;
        $this->router = $router;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $transformer = new DataTransformer\RennerNameToRennerIdTransformer($this->em, $this->rennerManager);
        //$builder->add('renner','text');
        $builder->addViewTransformer($transformer);
    }

    public function setDefaultOptions(\Symfony\Component\OptionsResolver\OptionsResolverInterface $resolver)
    {
        $url = $this->router->generate('get_riders');
        //$url = $this->router->generate('renner_search');
        $resolver->setDefaults(
            array('invalid_message' => 'De ingevulde renner is niet teruggevonden',
                'attr' => array(
                    'autocomplete' => 'off',
                    'data-link' => $url))
        );
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