<?php declare(strict_types=1);

namespace App\Command;

use App\Entity\Renner;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

ini_set('memory_limit', '1G');

class SlugRidersCommand extends Command
{
    public function __construct(private EntityManagerInterface $em, string $name = null)
    {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->setName('cyclear:slug-riders');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $qb = $this->em->getRepository(Renner::class)->createQueryBuilder('r')->where('r.slug IS NULL'); // ->setMaxResults(5000);
        $repo = $this->em->getRepository(Renner::class);
        foreach ($qb->getQuery()->getResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY) as $i => $renner) {
            $renner = $repo->find($renner['id']);
            $renner->setSlug(\Gedmo\Sluggable\Util\Urlizer::urlize($renner->getNaam()));
            if ($i % 250 == 0 && $i != 0) {
                $output->writeln(memory_get_usage(true));
                $output->writeln("$i; have to flush");
                $this->em->flush();
                $this->em->clear();
            }
            $output->writeln($renner->getId() . ' slugged');
            unset($renner);
        }
        $this->em->flush();
        return Command::SUCCESS;
    }
}
