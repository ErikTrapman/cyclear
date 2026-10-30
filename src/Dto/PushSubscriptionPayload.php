<?php declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * A browser's push subscription as public/js/push.js posts it.
 */
final readonly class PushSubscriptionPayload
{
    /**
     * The push services of Chrome (and other Chromium browsers), Firefox, Safari and Edge. The server posts to the
     * endpoint, so it must not be able to point anywhere else.
     */
    private const string PUSH_SERVICES = '#^https://(fcm\.googleapis\.com|updates\.push\.services\.mozilla\.com|web\.push\.apple\.com|[a-z0-9-]+\.notify\.windows\.com)/#';

    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(max: 500)]
        #[Assert\Regex(pattern: self::PUSH_SERVICES, message: 'Deze browser wordt niet ondersteund.')]
        public string $endpoint,
        #[Assert\NotBlank]
        #[Assert\Length(max: 100)]
        public string $p256dh,
        #[Assert\NotBlank]
        #[Assert\Length(max: 50)]
        public string $auth,
    ) {
    }
}
