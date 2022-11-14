<?php declare(strict_types=1);

namespace App\DataView;

use App\Entity\Renner;

class BloodHoundRiderView
{
    public function serialize(Renner $data): array
    {
        return [
            'identifier' => $data->getCQRankingId(),
            'name' => $data->getNaam(),
            'value' => $data->__toString(),
            'slug' => $data->getSlug(),
        ];
    }
}
