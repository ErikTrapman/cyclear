<?php declare(strict_types=1);

namespace App\Command;

use App\Entity\Renner;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CQTwitterFixerCommand extends Command
{
    public function __construct(private readonly EntityManagerInterface $em, string $name = null)
    {
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setName('cyclear:fixer:twitter')
            ->addOption('file', 'f', InputOption::VALUE_REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // RiderID;UCICode;Name;Twitter
        // TODO replace with Symfony CSV
//        $reader = new CsvReader(new \SplFileObject($input->getOption('file')), ';');
//        $reader->setHeaderRowNumber(0);

        $reader = [];
        $riderRepo = $this->em->getRepository(Renner::class);
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
            if ($i > 0 && 0 === $i % 100) {
                $this->em->flush();
                $output->writeln('Flushed');
            }
        }
        // Flush leftovers
        $this->em->flush();
        return Command::SUCCESS;
    }
}
