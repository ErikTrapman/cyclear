<?php

/*
 * This file is part of the Cyclear-game package.
 *
 * (c) Erik Trapman <veggatron@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cyclear\GameBundle\Command;

use Cyclear\GameBundle\Entity\Country;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class CountryBootstrapCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this->setName('cyclear:country-bootstrap')
            ->setDescription('Add countries in locales nl_NL and en_GB');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // add ISO-list of countries
        $kernel = $this->getContainer()->get('kernel');
        $yaml = Yaml::parse(file_get_contents(
            $kernel->getRootDir() . DIRECTORY_SEPARATOR . 'Resources/files/umpirsky/country-list/cldr.country.nl_NL.yaml'));

        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $cRepo = $em->getRepository("CyclearGameBundle:Country");

        foreach ($yaml as $iso => $name) {
            $c = $cRepo->findOneByIso2($iso);
            if (null === $c) {
                $c = new Country();
                $c->setIso2($iso);
                $c->setName($name);
                $c->setTranslatableLocale('nl_NL');
                $em->persist($c);
            }
        }
        $em->flush();

        $yamlEN = Yaml::parse(file_get_contents(
            $kernel->getRootDir() . DIRECTORY_SEPARATOR . 'Resources/files/umpirsky/country-list/cldr.country.en_GB.yaml'));
        foreach ($yamlEN as $iso => $name) {
            $country = $cRepo->findOneByIso2($iso);
            if (null !== $country) {
                $country->setTranslatableLocale('en_GB');
                $country->setName($name);
                $em->persist($country);
            } else {
                $output->writeln("$iso not added. Does exist in en_GB, but not in nl_NL");
            }
        }
        $em->flush();
    }
}
