<?php

namespace Cyclear\GameBundle\Filter;

use Doctrine\ORM\Query\Filter\SQLFilter;
use Doctrine\ORM\Mapping\ClassMetaData;

class SeizoenFilter extends SQLFilter
{

    // TODO verplaatsen naar Ploeg map
    public function addFilterConstraint(ClassMetaData $targetEntity, $targetTableAlias)
    {
        if ($targetEntity->name == 'Cyclear\GameBundle\Entity\Ploeg') {
            return $targetTableAlias.'.seizoen_id = ( SELECT s.id FROM Seizoen s WHERE s.slug = '.$this->getParameter('seizoen').')';
        }
        return "";
    }
}
?>
