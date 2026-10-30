<?php declare(strict_types=1);

namespace App\Message;

/**
 * Tells everyone with push notifications on about a transfer a manager made. Identified by the incoming transfer,
 * which links to the outgoing one.
 */
final readonly class TransferNotification
{
    public function __construct(
        public int $transferId,
    ) {
    }
}
