<?php declare(strict_types=1);

namespace App\Push;

/**
 * A notification as public/sw.js shows it; the url is opened when the notification is clicked.
 */
final readonly class PushMessage
{
    public function __construct(
        public string $title,
        public string $body,
        public string $url,
    ) {
    }
}
