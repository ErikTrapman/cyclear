<?php declare(strict_types=1);

namespace App\Filter;

use Doctrine\ORM\Mapping\ClassMetaData;
use Doctrine\ORM\Query\Filter\SQLFilter;

class SeizoenFilter extends SQLFilter
{
    // TODO verplaatsen naar Ploeg map
    public function addFilterConstraint(ClassMetaData $targetEntity, $targetTableAlias)
    {
        if ('App\Entity\Ploeg' == $targetEntity->name) {
            return $targetTableAlias . '.seizoen_id = ( SELECT s.id FROM Seizoen s WHERE s.slug = ' . $this->getParameter('seizoen') . ')';
        }
        return '';
    }
}
