<?php
/**
 * Created by PhpStorm.
 * User: flat
 * Date: 22-2-17
 * Time: 19:31
 */

namespace Cyclear\GameBundle\Command;


use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;

class StartlistStuffCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('cyclear:startlists');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $baseUrl = 'http://www.procyclingstats.com/rider.php?id=%d';
        $riders = [
            'bardet' => 140495,
            'guldhammer' => 139811
        ];

        $values = [];

        $dumper = function (Crawler $node, $i) use ($output, $values) {
            foreach ($node->getIterator() as $item) {
                if ('more' !== $item->nodeValue) {
                    $output->writeln($item->nodeValue);
                }
            }
        };

        foreach ($riders as $rider) {

            $crawler = new Crawler();
            $crawler->addContent(file_get_contents(sprintf($baseUrl, $rider)), 'text/html');
            $name = $crawler->filter('.entryHeader')->filter('h1')->getNode(0)->nodeValue;

            $output->writeln($name);

            try {
                $interestingNodes = $crawler->filter('.section')->first()->siblings();
            } catch (\InvalidArgumentException $e) {
                continue;
            }


            $interestingNodes->last()->children()->filter('a')->each(function (Crawler $node, $i) use ($output) {
                foreach ($node->getIterator() as $item) {
                    if ('more' !== $item->nodeValue) {
                        $output->writeln($item->nodeValue);
                    }
                }
            });
        }

    }

}