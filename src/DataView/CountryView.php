<?php declare(strict_types=1);

namespace App\DataView;

use Samson\Bundle\DataViewBundle\DataView\AbstractDataView;

class CountryView extends AbstractDataView
{
    /**
     * @return void
     */
    public function serialize($data, array $options = [])
    {
        $this->add('name', $data);
        $this->add('iso2', $data);
    }
}
