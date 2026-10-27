<?php declare(strict_types=1);

namespace App\Asset;

use Symfony\Component\Asset\VersionStrategy\VersionStrategyInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Versions assets with a hash of their content, so a changed file gets a new URL and browsers stop using the cached
 * copy, while unchanged files keep their URL.
 */
class ContentHashVersionStrategy implements VersionStrategyInterface
{
    /** @var array<string, string> */
    private array $versions = [];

    public function __construct(
        #[Autowire('%kernel.project_dir%/public')]
        private readonly string $publicDir,
    ) {
    }

    public function getVersion(string $path): string
    {
        if (!isset($this->versions[$path])) {
            $file = $this->publicDir . '/' . ltrim($path, '/');
            $this->versions[$path] = is_file($file) ? substr(hash_file('xxh3', $file), 0, 8) : '';
        }
        return $this->versions[$path];
    }

    public function applyVersion(string $path): string
    {
        $version = $this->getVersion($path);
        return '' === $version ? $path : $path . '?v=' . $version;
    }
}
