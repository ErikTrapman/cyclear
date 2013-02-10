<?php

namespace Cyclear\GameBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CQNationalityFixerCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this->setName('cyclear:cqnat-fixer')
            ->setDescription('Nationaliteit toevoegen obv CQ-nationaliteit code')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $kernel = $this->getContainer()->get('kernel');
        $file = new \SplFileObject( $kernel->getRootDir().DIRECTORY_SEPARATOR.'/Resources/files/cq/CQRiders.csv');

        $r = new \Ddeboer\DataImport\Reader\CsvReader($file, ",");
        $r->setHeaderRowNumber(0);

        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        $riderRepo = $em->getRepository("CyclearGameBundle:Renner");
        $countryRepo = $em->getRepository("CyclearGameBundle:Country");

        $resolver = new \ErikTrapman\Bundle\CQRankingParserBundle\Nationality\NationalityResolver();
        $transRepo = $em->getRepository('Gedmo\\Translatable\\Entity\\Translation');
        
        foreach ($r as $i => $row) {
            $country = null;
            if (!is_array($row)) {
                continue;
            }
            $cqId = $row['RiderID'];
            $countryName = $resolver->getFullNameFromCode($row['Nationality']);
            if (!strlen($countryName)) {
                $output->writeln("Skipped row ".$cqId);
                continue;
            }
            if (null === $countryName) {
                $output->writeln($row['Nationality']." not found in NationalityResolver");
                break;
            }
            $rider = $riderRepo->findOneByCQId($cqId);
            if(null === $rider){
                continue;
            } 
            $rider->setNaam($row["Name"]);
            $trans = $transRepo->findOneBy(array('content' => $countryName, 'locale' => 'en_GB'));
            if (null === $trans) {
                $country = $countryRepo->findOneByName($countryName);
            } else {
                $country = $countryRepo->find($trans->getForeignKey());
            }
            if(null === $country){
                $output->writeln("Unable to resolve $countryName from abbreviation ".$row['Nationality']);
                break;
            }
            $rider->setCountry($country);
            $em->persist($rider);
            if($i % 250 == 0 && $i != 0){
                $output->writeln("$i; have to flush");
                $em->flush();
                $em->clear();
            }
        }
        // flush the remaining changes
        $em->flush();
    }
}