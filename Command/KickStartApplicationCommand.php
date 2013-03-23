<?php

namespace Cyclear\GameBundle\Command;

use Cyclear\GameBundle\Entity\Periode;
use Cyclear\GameBundle\Entity\Seizoen;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class KickStartApplicationCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this->setName('cyclear:kickstart')
            ->setDescription('Start de applicatie op met tenminste 1 seizoen en 1 periode')
            ->addArgument('naam', InputArgument::REQUIRED, 'Naam van het seizoen');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $naam = $input->getArgument('naam');

        $em = $this->getContainer()->get('doctrine')->getManager();

        $s = new Seizoen();
        $s->setIdentifier($naam);
        $s->setClosed(0);
        $s->setCurrent(1);
        $em->persist($s);

        $p = new Periode();
        $date = new DateTime();
        $p->setStart($date);
        $p->setEind($date);
        $p->setSeizoen($s);
        $p->setTransfers(10);

        $em->persist($p);
        $em->flush();
        $output->write("Toegevoegd: 1 seizoen en 1 periode");
    }
}