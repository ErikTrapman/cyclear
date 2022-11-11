<?php declare(strict_types=1);

namespace App\Filter\Ploeg;

use Doctrine\ORM\Mapping\ClassMetaData;
use Doctrine\ORM\Query\Filter\SQLFilter;

class PloegNaamFilter extends SQLFilter
{
    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias, $targetTable = '')
    {
        if ($targetEntity->name != 'App\Entity\Ploeg') {
            return '';
        }
        return $targetTableAlias . '.naam LIKE \'%' . trim($this->getParameter('naam'), "'") . '%\'';
    }
}
