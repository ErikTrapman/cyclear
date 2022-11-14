<?php declare(strict_types=1);

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
     */
    public function __construct(EntityManagerInterface $em, RennerManager $rennerManager, RouterInterface $router)
    {
        $this->em = $em;
        $this->rennerManager = $rennerManager;
        $this->router = $router;
    }

    /**
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $transformer = new DataTransformer\RennerNameToRennerIdTransformer($this->em, $this->rennerManager);
        $builder->addViewTransformer($transformer);
    }

    /**
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $url = $this->router->generate('get_riders', ['_format' => 'json']);
        $resolver->setDefaults(
            ['invalid_message' => 'De ingevulde renner is niet teruggevonden',
                'attr' => [
                    'style' => 'width: 400px',
                    'autocomplete' => 'off',
                    'data-link' => $url, ],
            ]
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

    public function getName(): string
    {
        return 'renner_selector';
    }
}
