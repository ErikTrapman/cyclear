<?php declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * A transfer confirmed in the command palette, identified by rider slugs.
 */
final readonly class PaletteTransfer
{
    public function __construct(
        #[Assert\NotBlank]
        public string $rennerIn,
        #[Assert\NotBlank]
        public string $rennerUit,
        #[Assert\Length(max: 255)]
        public ?string $comment = null,
    ) {
    }
}
