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

use Cyclear\GameBundle\Entity\Seizoen;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CQAutomaticResultsResolverCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('cyclear:auto-results');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $resolver = $this->getContainer()->get('cyclear_game.cq.cqautomatic_results_resolver');
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        /** @var Seizoen $seizoen */
        $seizoen = $em->getRepository('CyclearGameBundle:Seizoen')->getCurrent();
        if (!$seizoen) {
            return;
        }
        $upTo = clone $seizoen->getEnd();
        $upTo->setTime(23, 59, 59);
        foreach ($resolver->resolve($seizoen, $upTo, 20) as $r) {
            $r->setFullyProcessed(true);
            $em->persist($r);
        }
        $em->flush();
    }


}