<?php
namespace Cyclear\GameBundle\DataView;


use Samson\Bundle\DataViewBundle\DataView\AbstractDataView;

class RiderSearchView extends AbstractDataView
{

    public function serialize($data, array $options = array())
    {
        $this->add('naam', $data[0]);
        $this->add('slug', $data[0]);
        $this->addFixed('punten', $data['punten']);
        $this->addFixed('team', $data['team'] == -1 ? null : $data['team']);
        $this->add(new CountryView(), $data[0]->getCountry(), 'country');
        return $this;
    }
}