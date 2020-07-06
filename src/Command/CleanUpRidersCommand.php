<?php

/*
 * This file is part of the Cyclear-game package.
 *
 * (c) Erik Trapman <veggatron@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Command;

use App\Entity\Contract;
use App\Entity\Renner;
use App\Entity\Transfer;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

ini_set('memory_limit', '1G');

class CleanUpRidersCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this->setName('cyclear:cleanup-riders');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $qb = $em->getRepository(Renner::class)->createQueryBuilder('r')->where('r.country IS NULL');
        foreach ($qb->getQuery()->getResult() as $i => $renner) {

            $qb = $em->getRepository(Transfer::class)
                ->createQueryBuilder('t')
                ->where('t.renner = :renner')
                ->setParameter('renner', $renner);
            foreach ($qb->getQuery()->getResult() as $transfer) {
                $em->remove($transfer);
            }

            $qb = $em->getRepository(Contract::class)
                ->createQueryBuilder('c')
                ->where('c.renner = :renner')
                ->setParameter('renner', $renner);
            foreach ($qb->getQuery()->getResult() as $contract) {
                $em->remove($contract);
            }
            $em->remove($renner);
        }
        $em->flush();

    }

}