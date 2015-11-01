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
        $seizoen = $em->getRepository('CyclearGameBundle:Seizoen')->getCurrent();
        $res = $resolver->resolve($seizoen, 100);

        foreach ($res as $r) {
            var_dump($r->getNaam());
            foreach ($r->getUitslagen() as $u) {
                var_dump($u->getRenner()->__toString());
            }
            // r->setFullyProcessed to true
            // persist
        }
        // flush

    }


}