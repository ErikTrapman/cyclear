<?php declare(strict_types=1);

namespace App\DataView;

class RiderSearchView
{
    public function serialize(array $data): array
    {
        return [
            'naam' => $data[0]->getNaam(),
            'slug' => $data[0]->getSlug(),
            'punten' => $data['punten'],
            'team' => ($data['team'] ?? false) == -1 ? null : $data['team'],
            'country' => [
                'name' => $data[0]->getCountry()->getName(),
                'iso2' => $data[0]->getCountry()->getIso2(),
            ]
        ];
    }
}
