<?php declare(strict_types=1);

namespace App\Tests\Listener;

use App\Tests\Fixtures\LoadSeizoenData;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RequestListenerTest extends WebTestCase
{
    public function testUnknownSeasonIsNotFound(): void
    {
        $client = static::createClient();
        self::getContainer()->get(DatabaseToolCollection::class)->get()->loadFixtures([LoadSeizoenData::class]);

        // matches the /{seizoen} route, but no season has this slug
        $client->request('GET', '/bestaat-niet');

        $this->assertResponseStatusCodeSame(404);
    }
}
