<?php

/*
 * This file is part of the Cyclear-game package.
 *
 * (c) Erik Trapman <veggatron@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cyclear\GameBundle\Listener\Doctrine;

use Cyclear\GameBundle\Entity\Transfer;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\Translation\TranslatorInterface;

class TransferListener
{
    private $tweeter;

    private $translator;

    private $tweetMsgs = [
        '%team% gives %out% the boot and welcomes %in% into the team!',
        '%team% says "Hi" to %in% and "Bye" to %out%',
        '%team% fires %out% and hires %in%',
        '%team% kicks %out% out in favour of %in%'
    ];

    public function __construct($tweeter, TranslatorInterface $translator)
    {
        $this->tweeter = $tweeter;
        $this->translator = $translator;
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
                $msg = $this->translator->trans($this->tweetMsgs[rand(0, 3)], $params);
                try {
                    $this->tweeter->sendTweet($msg);
                } catch (\Exception $e) {
                    // do nothing. Exception is logged
                }
            }
        }
    }
}