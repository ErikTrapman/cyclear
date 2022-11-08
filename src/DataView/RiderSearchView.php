<?php declare(strict_types=1);

namespace App\DataView;

use Samson\Bundle\DataViewBundle\DataView\AbstractDataView;

class RiderSearchView extends AbstractDataView
{
    /**
     * @return static
     */
    public function serialize($data, array $options = [])
    {
        $this->add('naam', $data[0]);
        $this->add('slug', $data[0]);
        $this->addFixed('punten', $data['punten']);
        if (isset($data['team'])) {
            $this->addFixed('team', $data['team'] == -1 ? null : $data['team']);
        }
        $this->add(new CountryView(), $data[0]->getCountry(), 'country');
        return $this;
    }
}
