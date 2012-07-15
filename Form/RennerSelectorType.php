<?php
namespace Cyclear\GameBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class RennerSelectorType extends AbstractType {
    
    /**
     *
     * @var \Symfony\Bundle\DoctrineBundle\Registry
     */
    private $em;

    public function __construct(\Doctrine\ORM\EntityManager $em) {
        $this->em = $em;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $transformer = new DataTransformer\RennerNameToRennerIdTransformer($this->em);
        //$builder->add('renner','text');
        $builder->appendClientTransformer($transformer);
    }

    public function getDefaultOptions() {
        return array(
            'invalid_message' => 'De ingevulde renner is niet teruggevonden'
        );
    }

    public function getParent() {
        return 'text';
    }

    public function getName() {
        return 'renner_selector';
    }

}