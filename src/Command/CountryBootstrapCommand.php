<?php declare(strict_types=1);

namespace App\Command;

use App\Entity\Country;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class CountryBootstrapCommand extends Command
{
    public function __construct(private EntityManagerInterface $em, string $name = null)
    {
        parent::__construct($name);
    }

    /**
     * @return void
     */
    protected function configure()
    {
        $this->setName('cyclear:country-bootstrap')
            ->setDescription('Add countries in locales nl_NL and en_GB');
    }

    /**
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // add ISO-list of countries
        $yaml = Yaml::parse(file_get_contents(
            __DIR__ . '/../Resources/files/umpirsky/country-list/cldr.country.nl_NL.yaml'));

        $cRepo = $this->em->getRepository(Country::class);

        foreach ($yaml as $iso => $name) {
            $c = $cRepo->findOneByIso2($iso);
            if (null === $c) {
                $c = new Country();
                $c->setIso2($iso);
                $c->setName($name);
                $c->setTranslatableLocale('nl_NL');
                $this->em->persist($c);
            }
        }
        $this->em->flush();

        $yamlEN = Yaml::parse(file_get_contents(
            __DIR__ . '../Resources/files/umpirsky/country-list/cldr.country.en_GB.yaml'));
        foreach ($yamlEN as $iso => $name) {
            $country = $cRepo->findOneByIso2($iso);
            if (null !== $country) {
                $country->setTranslatableLocale('en_GB');
                $country->setName($name);
                $this->em->persist($country);
            } else {
                $output->writeln("$iso not added. Does exist in en_GB, but not in nl_NL");
            }
        }
        $this->em->flush();
    }
}
