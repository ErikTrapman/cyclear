<?php

/*
 * This file is part of the Cyclear-game package.
 *
 * (c) Erik Trapman <veggatron@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Listener\Doctrine;

use App\Entity\Transfer;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\Translation\TranslatorInterface;

class TransferListener
{
    private $tweeter;

    private $translator;

    private $tweetMsgs = [
        '%team% gives %out% the boot and welcomes %in% into the team',
        '%team% says "Hi" to %in% and "Bye" to %out%',
        '%team% fires %out% and hires %in%',
        '%team% kicks %out% out in favour of %in%',
        '.%out% got the sack, %in% a contract at %team%',
        'High expectations at %team% for new signing %in%; %out% failed to extend'
    ];

    public function __construct($tweeter, TranslatorInterface $translator)
    {
        $this->tweeter = $tweeter;
        $this->translator = $translator;
    }

    private function getRandomTweet()
    {
        $index = rand(0, count($this->tweetMsgs) - 1);
        return $this->tweetMsgs[$index];
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $entityManager = $args->getEntityManager();

        if ($entity instanceof Transfer) {
            if (null !== $entity->getPloegNaar() && $entity->getTransferType() != Transfer::DRAFTTRANSFER) {
                $inversion = $entity->getInversionTransfer();
                $ploegNaar = $entity->getPloegNaar()->getAfkorting();
                $rennerUit = null;
                if (null !== $inversion) {
                    $rennerUit = $inversion->getRenner();
                }
                $rennerIn = $entity->getRenner();
                $rennerInDisplay = $rennerIn->getTwitter() ? '@' . $rennerIn->getTwitter() : $rennerIn->getNaam();
                $rennerUitDisplay = '';
                if ($rennerUit) {
                    $rennerUitDisplay = $rennerUit->getTwitter() ? '@' . $rennerUit->getTwitter() : $rennerUit->getNaam();
                }
                $params = ['%team%' => $ploegNaar, '%in%' => $rennerInDisplay, '%out%' => $rennerUitDisplay];
                $msg = $this->translator->trans($this->getRandomTweet(), $params);
                try {
                    $this->tweeter->sendTweet($msg);
                } catch (\Exception $e) {
                    // do nothing. Exception is logged
                }
            }
        }
    }


}