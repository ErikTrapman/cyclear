<?php

namespace Cyclear\GameBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SlugRidersCommand extends ContainerAwareCommand
{
    
    protected function configure()
    {
        $this->setName('cyclear:slug-riders')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $qb = $em->getRepository("CyclearGameBundle:Renner")->createQueryBuilder('r')->where('r.slug IS NULL');//->setMaxResults(5000);
        $repo = $em->getRepository("CyclearGameBundle:Renner");
        foreach($qb->getQuery()->getResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY) as $i => $renner ){
            $renner = $repo->find($renner['id']);
            $renner->setSlug(\Gedmo\Sluggable\Util\Urlizer::urlize($renner->getNaam()));
            $em->persist($renner);
            if($i % 250 == 0 && $i != 0){
                $output->writeln("$i; have to flush");
                $em->flush();
                $em->clear();
            }
            $output->writeln($renner->getId()." slugged");
            unset($renner);
        }
        $em->flush();
        
    }
    
}