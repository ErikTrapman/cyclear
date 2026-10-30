<?php declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * The web app manifest that makes Cyclear installable. Rendered instead of a static file, so it's never served from a
 * cache and its icons get content-hash URLs: a changed icon reaches phones on the next install.
 */
class ManifestController extends AbstractController
{
    // Before the game route, which would take "manifest.webmanifest" for a season
    #[Route(path: '/manifest.webmanifest', name: 'app_manifest', methods: ['GET'], priority: 1)]
    public function manifestAction(): Response
    {
        $response = $this->render('pwa/manifest.webmanifest.twig');
        $response->headers->set('Content-Type', 'application/manifest+json');

        return $response;
    }
}
