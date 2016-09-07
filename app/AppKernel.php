<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    public function registerBundles()
    {

        $bundles = array(

            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),

            new JMS\AopBundle\JMSAopBundle(),
            new JMS\DiExtraBundle\JMSDiExtraBundle($this),

            new JMS\SerializerBundle\JMSSerializerBundle(),
            new \Symfony\Bundle\AsseticBundle\AsseticBundle(),

            new Cyclear\GameBundle\CyclearGameBundle(),

            new FOS\UserBundle\FOSUserBundle(),
            new Knp\Bundle\PaginatorBundle\KnpPaginatorBundle(),
            new Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle(),

            #new Samson\Bundle\FilterBundle\SamsonFilterBundle(),
            new ErikTrapman\Bundle\CQRankingParserBundle\ErikTrapmanCQRankingParserBundle(),
            new SunCat\MobileDetectBundle\MobileDetectBundle(),
            new Cyclear\ApplicationBundle\CyclearApplicationBundle(),

            new \FOS\JsRoutingBundle\FOSJsRoutingBundle(),
            //new \Samson\Bundle\AutocompleteBundle\SamsonAutocompleteBundle(),
            new \Samson\Bundle\DataViewBundle\SamsonDataViewBundle(),
            new \Bmatzner\FontAwesomeBundle\BmatznerFontAwesomeBundle(),
            new \Vich\UploaderBundle\VichUploaderBundle(),
            new JMS\SecurityExtraBundle\JMSSecurityExtraBundle()
        );

        if (in_array($this->getEnvironment(), array('dev', 'test'))) {
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
            $bundles[] = new Liip\FunctionalTestBundle\LiipFunctionalTestBundle();
        }

        return $bundles;
    }

    public function getRootDir()
    {
        return __DIR__;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__ . '/config/config_' . $this->getEnvironment() . '.yml');
    }
}
