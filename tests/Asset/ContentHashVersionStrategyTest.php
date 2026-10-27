<?php declare(strict_types=1);

namespace App\Tests\Asset;

use App\Asset\ContentHashVersionStrategy;
use PHPUnit\Framework\TestCase;

class ContentHashVersionStrategyTest extends TestCase
{
    private string $dir;

    protected function setUp(): void
    {
        $this->dir = sys_get_temp_dir() . '/cyclear-assets-' . bin2hex(random_bytes(4));
        mkdir($this->dir . '/css', 0777, true);
    }

    protected function tearDown(): void
    {
        @unlink($this->dir . '/css/style.css');
        @rmdir($this->dir . '/css');
        @rmdir($this->dir);
    }

    public function testVersionFollowsTheFileContent(): void
    {
        file_put_contents($this->dir . '/css/style.css', 'body { color: red; }');
        $first = (new ContentHashVersionStrategy($this->dir))->applyVersion('css/style.css');
        $this->assertMatchesRegularExpression('#^css/style\.css\?v=[0-9a-f]{8}$#', $first);
        $this->assertSame($first, (new ContentHashVersionStrategy($this->dir))->applyVersion('css/style.css'));

        file_put_contents($this->dir . '/css/style.css', 'body { color: blue; }');
        $this->assertNotSame($first, (new ContentHashVersionStrategy($this->dir))->applyVersion('css/style.css'));
    }

    public function testMissingFileIsLeftUnversioned(): void
    {
        $this->assertSame('css/missing.css', (new ContentHashVersionStrategy($this->dir))->applyVersion('css/missing.css'));
    }
}
