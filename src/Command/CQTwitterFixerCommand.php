<?php
namespace App\Command;

use App\Entity\Renner;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CQTwitterFixerCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('cyclear:fixer:twitter')
            ->addOption('file', 'f', InputOption::VALUE_REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // RiderID;UCICode;Name;Twitter
        // TODO replace with Symfony CSV
//        $reader = new CsvReader(new \SplFileObject($input->getOption('file')), ';');
//        $reader->setHeaderRowNumber(0);

        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $reader = [];
        $riderRepo = $em->getRepository(Renner::class);
        foreach ($reader as $i => $row) {
            $cqId = $row['RiderID'];
            $rider = $riderRepo->findOneByCQId($cqId);
            if (null === $rider) {
                continue;
            }
            if (null !== $rider->getTwitter()) {
                continue;
            }
            $handle = trim($row['Twitter']);
            if (!$handle) {
                continue;
            }
            $rider->setTwitter($handle);
            $em->persist($rider);
            if ($i > 0 && 0 === $i % 100) {
                $em->flush();
                $output->writeln('Flushed');
            }
        }
        $em->flush();
    }


}
