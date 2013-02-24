<?php

namespace Cyclear\GameBundle\Form;

use Cyclear\GameBundle\EntityManager\RennerManager;
use Cyclear\GameBundle\Form\Helper\PreBindValueTransformer;
use Cyclear\GameBundle\Form\UitslagType;
use Cyclear\GameBundle\Form\WedstrijdType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Event\DataEvent;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UitslagCreateType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $wedstrijdManager = $options['wedstrijd_manager'];
        $uitslagManager = $options['uitslag_manager'];
        $crawlerManager = $options['crawler_manager'];
        $request = $options['request'];
        $seizoen = $options['seizoen'];
        $rennerManager = $options['renner_manager'];
        $builder
            ->add('url', 'eriktrapman_cqrankingmatchselector_type', array('mapped' => false, 'required' => true, 'label' => 'CQ-wedstrijd'))
            ->add('referentiewedstrijd', 'entity', array('required' => false, 'mapped' => false, 'class' => 'CyclearGameBundle:Wedstrijd',
                'query_builder' => function( EntityRepository $r ) {
                    return $r->createQueryBuilder('w')
                        ->join('w.uitslagtype', 'ut')
                        ->where('ut.isGeneralClassification = 0')
                        ->add('orderBy', 'w.id DESC')
                        ->setMaxResults(30);
                }))
            ->add('wedstrijd', new WedstrijdType())
        ;


        $factory = $builder->getFormFactory();

        $builder->addEventListener(FormEvents::PRE_BIND, function(DataEvent $e) use ($factory,
            $wedstrijdManager, $uitslagManager, $crawlerManager, $request, $seizoen) {
                $form = $e->getForm();
                $data = $e->getData();
                if (null === $data) {
                    return;
                }
                $helper = new PreBindValueTransformer();
                $uitslagType = $helper->transformPostedValue($data['wedstrijd']['uitslagtype'], $form->get('wedstrijd')->get('uitslagtype'));
                $referentieWedstrijd = $helper->transformPostedValue($data['referentiewedstrijd'], $form->get('referentiewedstrijd'));
                $datum = $helper->transformPostedValue($data['wedstrijd']['datum'], $form->get('wedstrijd')->get('datum'));
                $form->add($factory->createNamed('uitslag', 'collection', null, array('type' => new UitslagType(),
                        'allow_add' => true,
                        'by_reference' => false,
                        'options' => array(
                            'use_wedstrijd' => false,
                            'seizoen' => $seizoen))));
                if ($request->isXmlHttpRequest()) {
                    $url = $data['url'];
                    $crawler = $crawlerManager->getCrawler($url);
                    $wedstrijd = $wedstrijdManager->createWedstrijdFromCrawler($crawler, $datum);
                    $data['wedstrijd']['naam'] = $wedstrijd->getNaam();
                    $refDatum = ( null !== $referentieWedstrijd ) ? $referentieWedstrijd->getDatum() : null;
                    $data['uitslag'] = $uitslagManager->prepareUitslagen($uitslagType, $crawler, $wedstrijd, $refDatum);
                    $e->setData($data);
                }
            });


        $builder->addEventListener(FormEvents::POST_BIND, function(DataEvent $e) use($request, $rennerManager) {
                $form = $e->getForm();
                $data = $e->getData();
                if (null === $data) {
                    return;
                }
                $postData = $request->request->get($form->getName());
                if (array_key_exists('uitslag', $postData)) {
                    $wedstrijd = $data['wedstrijd'];
                    $uitslagen = $postData['uitslag'];
                    foreach ($data['uitslag'] as $index => $uitslag) {
                        // we gebruiken het uitslagen-form hierboven zonder 'Wedstrijd'
                        $uitslag->setWedstrijd($wedstrijd);
                    }
                }
            });
    }

    public function getName()
    {
        return 'cyclear_gamebundle_uitslagcreatetype';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'wedstrijd_manager' => null,
            'uitslag_manager' => null,
            'crawler_manager' => null,
            'renner_manager' => null,
            'request' => null,
            'seizoen' => null,
        ));
    }
}
