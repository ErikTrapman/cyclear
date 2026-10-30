<?php declare(strict_types=1);

namespace App\Command;

use Minishlink\WebPush\VAPID;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Browsers tie a push subscription to the public key, so replacing the keys turns notifications off for everyone.
 */
#[AsCommand(name: 'cyclear:push:vapid-keys', description: 'Generates a key pair to sign push notifications with')]
class PushVapidKeysCommand
{
    public function __invoke(OutputInterface $output): int
    {
        $keys = VAPID::createVapidKeys();
        $output->writeln('VAPID_PUBLIC_KEY=' . $keys['publicKey']);
        $output->writeln('VAPID_PRIVATE_KEY=' . $keys['privateKey']);

        return Command::SUCCESS;
    }
}
