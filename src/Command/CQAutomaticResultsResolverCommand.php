<?php declare(strict_types=1);

namespace App\Command;

use App\CQRanking\CQAutomaticResultsResolver;
use App\CQRanking\Parser\RecentRaces\RecentRacesParser;
use App\Entity\Seizoen;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CQAutomaticResultsResolverCommand extends Command
{
    use LockableTrait;

    protected static $defaultName = 'cyclear:auto-results';

    public function __construct(
        private readonly CQAutomaticResultsResolver $resolver,
        private readonly RecentRacesParser $parser,
        private readonly ManagerRegistry $doctrine,
        string $name = null
    ) {
        parent::__construct($name);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->lock()) {
            return 0;
        }

        $em = $this->doctrine->getManager();

        try {
            /** @var Seizoen $seizoen */
            $seizoen = $this->doctrine->getRepository(Seizoen::class)->getCurrent();
            if (!$seizoen) {
                return 1;
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
        return 0;
    }
}
