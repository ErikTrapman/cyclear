<?php declare(strict_types=1);

namespace App\Tests\Push;

use Symfony\Component\HttpClient\Response\MockResponse;

/**
 * Stands in for the browsers' push services: records every notification and answers 410 Gone for endpoints ending in
 * "/expired", like a push service does for a subscription the user revoked.
 */
class PushServiceMock
{
    /** @var array<string, string> encrypted payload by endpoint */
    public array $sent = [];

    /**
     * @param array<string, mixed> $options
     */
    public function __invoke(string $method, string $url, array $options = []): MockResponse
    {
        $this->sent[$url] = (string)($options['body'] ?? '');
        return new MockResponse('', ['http_code' => str_ends_with($url, '/expired') ? 410 : 201]);
    }
}
