<?php

/*
 * This file is part of the Cyclear-game package.
 *
 * (c) Erik Trapman <veggatron@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Form;

use App\EntityManager\RennerManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;

class RennerSelectorType extends AbstractType
{
    /**
     *
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var RennerManager
     */
    private $rennerManager;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @param \Doctrine\ORM\EntityManager $em
     * @param RennerManager $rennerManager
     * @param RouterInterface $router
     */
    public function __construct(EntityManagerInterface $em, RennerManager $rennerManager, RouterInterface $router)
    {
        $this->em = $em;
        $this->rennerManager = $rennerManager;
        $this->router = $router;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $transformer = new DataTransformer\RennerNameToRennerIdTransformer($this->em, $this->rennerManager);
        $builder->addViewTransformer($transformer);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $url = $this->router->generate('get_riders', array('_format' => 'json'));
        $resolver->setDefaults(
            array('invalid_message' => 'De ingevulde renner is niet teruggevonden',
                'attr' => array(
                    'autocomplete' => 'off',
                    'data-link' => $url))
        );
    }

    public function getParent()
    {
        return TextType::class;
    }

    public function getBlockPrefix()
    {
        return 'renner_selector';
    }

    public function getName()
    {
        return 'renner_selector';
    }
}