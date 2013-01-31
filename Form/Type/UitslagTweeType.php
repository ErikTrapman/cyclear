<?php

namespace Cyclear\GameBundle\Form\Type;

use Cyclear\GameBundle\Form\UitslagPrepareType;
use Cyclear\GameBundle\Form\WedstrijdType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Event\DataEvent;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;

class UitslagTweeType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $wedstrijdManager = $options['wedstrijd_manager'];
        $uitslagManager = $options['uitslag_manager'];
        $crawlerManager = $options['crawler_manager'];
        $request = $options['request'];
        $builder
            //->add('url', 'text', array('attr' => array('size' => 100), 'mapped' => false, 'required' => false))
            ->add('cq_wedstrijdid', 'text', array('mapped' => false, 'required' => false, 'label' => 'CQ-id'))
            ->add('url', 'eriktrapman_cqrankingmatchselector_type', array('mapped' => false, 'required' => true, 'label' => 'CQ-wedstrijd'))
            ->add('referentiewedstrijd', 'entity', array('required' => false, 'mapped' => false, 'class' => 'CyclearGameBundle:Wedstrijd',
                'query_builder' => function( \Doctrine\ORM\EntityRepository $r ) {
                    return $r->createQueryBuilder('w')
                        ->add('orderBy', 'w.id DESC')
                        ->setMaxResults(30);
                }))
            ->add('wedstrijd', new WedstrijdType())
        ;


        $factory = $builder->getFormFactory();

        $builder->addEventListener(FormEvents::PRE_BIND, function(DataEvent $e) use ($factory, $builder,
            $wedstrijdManager, $uitslagManager, $crawlerManager, $request) {
                $form = $e->getForm();
                $data = $e->getData();
                if (null === $data) {
                    return;
                }
                $uitslagType = $data['wedstrijd']['uitslagtype'];
                // TODO verplaatsen zodat dit voor de eeuwigheid blijft behouden
                foreach ($form->get('wedstrijd')->get('uitslagtype')->getClientTransformers() as $transformer) {
                    $uitslagType = $transformer->reverseTransform($uitslagType);
                }
                foreach ($form->get('wedstrijd')->get('uitslagtype')->getNormTransformers() as $transformer) {
                    $uitslagType = $transformer->reverseTransform($uitslagType);
                }
                $form->add($factory->createNamed('uitslag', 'collection', null, array('type' => new UitslagPrepareType(),
                        'allow_add' => true,
                        'by_reference' => false)));
                if ($request->isXmlHttpRequest()) {
                    $url = $data['url'];
                    $crawler = $crawlerManager->getCrawler($url);
                    $wedstrijd = $wedstrijdManager->createWedstrijdFromCrawler($crawler);
                    $data['wedstrijd']['naam'] = $wedstrijd->getNaam();
                    $data['uitslag'][0]['positie'] = 1;
                    $data['uitslag'][0]['renner'] = '[16898] JULES Justins';
                    $data['uitslag'][0]['ploeg'] = null;
                    $data['uitslag'][0]['ploegPunten'] = 100;
                    $data['uitslag'][0]['rennerPunten'] = 600;
                    $e->setData($data);
                }
            });
        $builder->addEventListener(FormEvents::POST_BIND, function(DataEvent $e) use ($factory, $builder) {
                $form = $e->getForm();
                $data = $e->getData();
                if (null === $data) {
                    return;
                }
                // TODO uitslagen->setWedstrijd()
                //$data['wedstrijd']->setNaam("AAAAA");
                //$e->setData($data);
            });
    }

    public function getName()
    {
        return 'cyclear_gamebundle_uitslagtweetype';
    }

    public function setDefaultOptions(\Symfony\Component\OptionsResolver\OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array('wedstrijd_manager' => null, 'uitslag_manager' => null, 'crawler_manager' => null, 'request' => null));
    }
}
