<?php declare(strict_types=1);

/*
 * This file is part of the Cyclear-game package.
 *
 * (c) Erik Trapman <veggatron@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Command;

use App\Entity\Seizoen;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\LockHandler;

class CQAutomaticResultsResolverCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('cyclear:auto-results');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $lockHandler = new LockHandler($this->getName());
        if (!$lockHandler->lock()) {
            return 0;
        }

        $resolver = $this->getContainer()->get('cyclear_game.cq.cqautomatic_results_resolver');
        $em = $this->getContainer()->get('doctrine')->getManager();
        /** @var Seizoen $seizoen */
        $seizoen = $em->getRepository(Seizoen::class)->getCurrent();
        if (!$seizoen) {
            return;
        }
        $start = clone $seizoen->getStart();
        $end = clone $seizoen->getEnd();
        $parser = $this->getContainer()->get('eriktrapman_cqparser.recentracesparser');
        $races = $parser->getRecentRaces();

        foreach ($resolver->resolve($races, $seizoen, $start, $end, 100) as $r) {
            $r->setFullyProcessed(true);
            $em->persist($r);
        }
        $em->flush();
    }
}
