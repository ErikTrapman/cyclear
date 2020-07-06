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


use App\Entity\Seizoen;
use App\Entity\Wedstrijd;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CQRecentRacesFixerCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('cyclear:fixer:recentraces');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // sets race-identifiers for all races on the recentraces page that can be found in the DB
        $parser = $this->getContainer()->get('eriktrapman_cqparser.recentracesparser');
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        /** @var Seizoen $seizoen */
        $seizoen = $em->getRepository(Seizoen::class)->getCurrent();
        $repo = $em->getRepository(Wedstrijd::class)->createQueryBuilder('w');
        $res = $parser->getRecentRaces();
        $output->writeln('Found ' . count($res) . ' results when resolving');
        foreach ($res as $r) {
            $output->writeln('resolving ' . $r->name);
            $transliterator = \Transliterator::createFromRules(':: NFD; :: [:Nonspacing Mark:] Remove; :: NFC;', \Transliterator::FORWARD);
            $test = $transliterator->transliterate($r->name);
            $existing = $repo->where($repo->expr()->like('w.naam', ":naam"))->setParameter('naam', $test . '%')
                ->andWhere('w.seizoen = :seizoen')->setParameter('seizoen', $seizoen)->getQuery();
            $existing = $existing->getOneOrNullResult();
            if ($existing) {
                $existing->setExternalIdentifier($r->url);
                $existing->setFullyProcessed(true);
                $em->persist($existing);
            } else {
                $output->writeln('Unable to find ' . $test);
            }
        }
        $em->flush();
    }


}