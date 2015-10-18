<?php
namespace Cyclear\GameBundle\DataView;

use Cyclear\GameBundle\Entity\Renner;
use Samson\Bundle\DataViewBundle\DataView\AbstractDataView;

class BloodHoundRiderView extends AbstractDataView
{
    /**
     * @param Renner $data
     * @param array $options
     */
    public function serialize($data, array $options = array())
    {
        $this->addFixed('identifier', $data->getCqRankingId());
        $this->addFixed('name', $data->getNaam());
        $this->addFixed('value', $data->__toString());
        $this->addFixed('slug', $data->getSlug());
        return $this;
    }


}