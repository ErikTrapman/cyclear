<?php declare(strict_types=1);

namespace App\Form;

use App\CQRanking\Parser\Crawler\CrawlerManager;
use App\Entity\Wedstrijd;
use App\EntityManager\UitslagManager;
use App\EntityManager\WedstrijdManager;
use App\Form\Helper\PreBindValueTransformer;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UitslagCreateType extends AbstractType
{
    public function __construct(
        private readonly WedstrijdManager $wedstrijdManager,
        private readonly UitslagManager $uitslagManager,
        private readonly CrawlerManager $crawlerManager,
    ) {
    }

    /**
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $request = $options['request'];
        $seizoen = $options['seizoen'];
        $builder
            ->add('url', MatchSelectorType::class, ['mapped' => false, 'required' => false, 'label' => 'CQ-wedstrijd'])
            ->add('url_manual', null, ['mapped' => false, 'required' => false, 'label' => 'URL', 'attr' => ['size' => 80]])
            ->add('referentiewedstrijd', EntityType::class, ['required' => false, 'mapped' => false, 'class' => Wedstrijd::class,
                'query_builder' => function (EntityRepository $r) {
                    return $r->createQueryBuilder('w')
                        ->where('w.generalClassification = 0')
                        ->add('orderBy', 'w.id DESC')
                        ->setMaxResults(90);
                }, ])
            ->add('wedstrijd', WedstrijdType::class, ['default_date' => $options['default_date']]);

        $factory = $builder->getFormFactory();

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $e) use (
            $factory,
            $request,
            $seizoen
        ) {
            $form = $e->getForm();
            $data = $e->getData();
            if (null === $data) {
                return;
            }
            $helper = new PreBindValueTransformer();
            $uitslagType = $helper->transformPostedValue($data['wedstrijd']['uitslagtype'], $form->get('wedstrijd')->get('uitslagtype'));
            $referentieWedstrijd = $helper->transformPostedValue($data['referentiewedstrijd'], $form->get('referentiewedstrijd'));
            $datum = $helper->transformPostedValue($data['wedstrijd']['datum'], $form->get('wedstrijd')->get('datum'));
            $form->add(
                $factory->createNamed('uitslag', CollectionType::class, null, [
                        'entry_type' => UitslagType::class,
                        'allow_add' => true,
                        'auto_initialize' => false,
                        'by_reference' => false,
                        'entry_options' => [
                            'use_wedstrijd' => false,
                            'seizoen' => $seizoen,
                        ],
                    ]
                )
            );
            if ($request->isXmlHttpRequest()) {
                $url = $data['url'] ? $data['url'] : $data['url_manual'];
                $crawler = $this->crawlerManager->getCrawler($url);
                $wedstrijd = $this->wedstrijdManager->createWedstrijdFromCrawler($crawler, $datum);
                $data['wedstrijd']['naam'] = $wedstrijd->getNaam();
                $data['wedstrijd']['uitslagtype'] = $uitslagType;
                $refDatum = (null !== $referentieWedstrijd) ? $referentieWedstrijd->getDatum() : null;
                $data['uitslag'] = $this->uitslagManager->prepareUitslagen($uitslagType, $crawler, $wedstrijd, $seizoen, $refDatum);
                $e->setData($data);
            }
        });

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $e) use ($request) {
            $form = $e->getForm();
            $data = $e->getData();
            if (null === $data) {
                return;
            }
            $postData = $request->request->get($form->getName());
            if (array_key_exists('uitslag', $postData)) {
                $wedstrijd = $data['wedstrijd'];
                foreach ($data['uitslag'] as $uitslag) {
                    // we gebruiken het uitslagen-form hierboven zonder 'Wedstrijd'
                    $uitslag->setWedstrijd($wedstrijd);
                }
            }
        });
    }

    /**
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'request' => null,
            'seizoen' => null,
            'default_date' => null,
        ]);
    }
}
