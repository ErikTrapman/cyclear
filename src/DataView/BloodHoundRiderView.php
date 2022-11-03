<?php declare(strict_types=1);

namespace App\DataView;

use App\Entity\Renner;
use Samson\Bundle\DataViewBundle\DataView\AbstractDataView;

class BloodHoundRiderView extends AbstractDataView
{
    public function serialize($data, array $options = [])
    {
        $this->addFixed('identifier', $data->getCqRankingId());
        $this->addFixed('name', $data->getNaam());
        $this->addFixed('value', $data->__toString());
        $this->addFixed('slug', $data->getSlug());
        return $this;
    }
}
