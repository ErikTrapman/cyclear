<?php
namespace App\DataView;

use Samson\Bundle\DataViewBundle\DataView\AbstractDataView;

class CountryView extends AbstractDataView
{
    public function serialize($data, array $options = array())
    {
        $this->add('name', $data);
        $this->add('iso2', $data);
    }


} 