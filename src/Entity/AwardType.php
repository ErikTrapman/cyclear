<?php declare(strict_types=1);

namespace App\Entity;

enum AwardType: string
{
    case SeasonWinner = 'season_winner';

    public function getIcon(): string
    {
        return match ($this) {
            self::SeasonWinner => '🏆',
        };
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::SeasonWinner => 'Winnaar',
        };
    }
}
