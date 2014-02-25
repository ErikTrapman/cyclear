<?php
namespace Cyclear\ApplicationBundle\Command;


use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DbCleanUpCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('cyclear:app:db-cleanup');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        $em = $container->get('doctrine.orm.entity_manager');
        $em->getConnection()->executeQuery("DELETE FROM AppLog WHERE time < DATE_SUB(NOW(), INTERVAL 30 DAY)");
    }
}