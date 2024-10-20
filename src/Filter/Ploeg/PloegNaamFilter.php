<?php declare(strict_types=1);

namespace App\Filter\Ploeg;

use Doctrine\ORM\Mapping\ClassMetaData;
use Doctrine\ORM\Query\Filter\SQLFilter;

class PloegNaamFilter extends SQLFilter
{
    public function addFilterConstraint(ClassMetaData $targetEntity, $targetTableAlias, $targetTable = '')
    {
        if ('App\Entity\Ploeg' != $targetEntity->name) {
            return '';
        }
        return $targetTableAlias . '.naam LIKE \'%' . trim($this->getParameter('naam'), "'") . '%\'';
    }
}
