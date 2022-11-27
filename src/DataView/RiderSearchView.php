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
            'team' => array_key_exists('team', $data) ? $data['team'] : null,
            'country' => [
                'name' => $data[0]->getCountry()->getName(),
                'iso2' => $data[0]->getCountry()->getIso2(),
            ],
        ];
    }
}
