<?php declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\Seizoen;
use App\Tests\Fixtures\LoadSeizoenData;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * The web app manifest phones read when Cyclear is installed.
 */
class ManifestTest extends WebTestCase
{
    public function testIconsHaveContentHashUrls(): void
    {
        $client = static::createClient();
        self::getContainer()->get(DatabaseToolCollection::class)->get()->loadFixtures([LoadSeizoenData::class]);
        $seizoen = self::getContainer()->get('doctrine')->getRepository(Seizoen::class)->findOneBy([]);

        $crawler = $client->request('GET', '/' . $seizoen->getSlug() . '/spelregels');
        $client->request('GET', $crawler->filter('link[rel="manifest"]')->attr('href'));

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/manifest+json');
        $manifest = json_decode((string)$client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame('Cyclear', $manifest['name']);
        $this->assertSame('standalone', $manifest['display']);
        $this->assertCount(3, $manifest['icons']);
        foreach ($manifest['icons'] as $icon) {
            // A changed icon gets a new URL, so phones don't install a cached copy of the old one
            $this->assertMatchesRegularExpression('#^/icons/app-[a-z0-9-]+\.png\?v=[0-9a-f]{8}$#', $icon['src']);
            $this->assertFileExists(self::getContainer()->getParameter('kernel.project_dir') . '/public' . strtok($icon['src'], '?'));
        }
    }
}
