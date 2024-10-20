<?php declare(strict_types=1);

namespace App\Command;

use App\CQRanking\CQAutomaticResultsResolver;
use App\CQRanking\Parser\RecentRaces\RecentRacesParser;
use App\Repository\SeizoenRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'cyclear:auto-results')]
class CQAutomaticResultsResolverCommand extends Command
{
    use LockableTrait;

    public function __construct(
        private readonly CQAutomaticResultsResolver $resolver,
        private readonly RecentRacesParser $parser,
        private readonly ManagerRegistry $doctrine,
        private readonly SeizoenRepository $seizoenRepository,
        ?string $name = null,
    ) {
        parent::__construct($name);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->lock()) {
            return 0;
        }

        $em = $this->doctrine->getManager();

        try {
            $seizoen = $this->seizoenRepository->getCurrent();
            if (!$seizoen) {
                return Command::SUCCESS;
            }
            $start = clone $seizoen->getStart();
            $end = clone $seizoen->getEnd();
            $races = $this->parser->getRecentRaces();

            foreach ($this->resolver->resolve($races, $seizoen, $start, $end, 100) as $match) {
                $match->setFullyProcessed(true);
                $em->persist($match);
            }
            $em->flush();
        } finally {
            $this->release();
        }
        return Command::SUCCESS;
    }
}
