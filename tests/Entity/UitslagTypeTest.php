<?php declare(strict_types=1);

namespace App\Tests\Entity;

use App\CQRanking\Parser\Strategy\Y2013\Stage;
use App\Entity\UitslagType;
use PHPUnit\Framework\TestCase;

class UitslagTypeTest extends TestCase
{
    public function testParsingStrategyIsStoredByClassName(): void
    {
        $type = new UitslagType();
        $this->assertNull($type->getCqParsingStrategy());

        $type->setCqParsingStrategy(new Stage());
        $this->assertInstanceOf(Stage::class, $type->getCqParsingStrategy());

        $type->setCqParsingStrategy(null);
        $this->assertNull($type->getCqParsingStrategy());
    }
}
