<?php
namespace Cyclear\GameBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class RennerSelectorType extends \Symfony\Component\Form\AbstractType {
    
    /**
     *
     * @var \Symfony\Bundle\DoctrineBundle\Registry
     */
    private $em;

    public function __construct(\Doctrine\ORM\EntityManager $em) {
        $this->em = $em;
    }

    public function buildForm(FormBuilder $builder, array $options) {
        $transformer = new DataTransformer\RennerNameToRennerIdTransformer($this->em);
        //$builder->add('renner','text');
        $builder->appendClientTransformer($transformer);
    }

    public function getDefaultOptions(array $options) {
        return array(
            'invalid_message' => 'De ingevulde renner is niet teruggevonden'
        );
    }

    public function getParent(array $options) {
        return 'text';
    }

    public function getName() {
        return 'renner_selector';
    }

}