<?php

namespace Cyclear\GameBundle\Form\Type;

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

class UitslagTweeType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $wedstrijdManager = $options['wedstrijd_manager'];
        $uitslagManager = $options['uitslag_manager'];
        $crawlerManager = $options['crawler_manager'];
        $request = $options['request'];
        $seizoen = $options['seizoen'];
        $builder
            //->add('cq_wedstrijdid', 'text', array('mapped' => false, 'required' => false, 'label' => 'CQ-id'))
            ->add('url', 'eriktrapman_cqrankingmatchselector_type', array('mapped' => false, 'required' => true, 'label' => 'CQ-wedstrijd'))
            ->add('referentiewedstrijd', 'entity', array('required' => false, 'mapped' => false, 'class' => 'CyclearGameBundle:Wedstrijd',
                'query_builder' => function( EntityRepository $r ) {
                    return $r->createQueryBuilder('w')
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
                    $uitslagen = $uitslagManager->prepareUitslagenTwee($uitslagType, $crawler, $wedstrijd, $refDatum);
                    $rennerManager = new RennerManager();
                    foreach ($uitslagen as $key => $uitslag) {
                        $data['uitslag'][$key]['positie'] = $uitslag->getPositie();
                        $data['uitslag'][$key]['renner'] = ( null !== $uitslag->getRenner() ) ? $rennerManager->getRennerSelectorTypeStringFromRenner($uitslag->getRenner()) : null;
                        $data['uitslag'][$key]['ploeg'] = ( null !== $uitslag->getPloeg() ) ? $uitslag->getPloeg()->getId() : null;
                        $data['uitslag'][$key]['ploegPunten'] = $uitslag->getPloegPunten();
                        $data['uitslag'][$key]['rennerPunten'] = $uitslag->getRennerPunten();
                    }
                    $e->setData($data);
                }
            });
    }

    public function getName()
    {
        return 'cyclear_gamebundle_uitslagtweetype';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'wedstrijd_manager' => null,
            'uitslag_manager' => null,
            'crawler_manager' => null,
            'request' => null,
            'seizoen' => null,
        ));
    }
}
