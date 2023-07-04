<?php declare(strict_types=1);

namespace App\Filter;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;

class RennerNaamFilter extends SQLFilter
{
    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias)
    {
        if ('App\Entity\Renner' != $targetEntity->name) {
            return '';
        }

        $value = $this->getParameter('naam');
        $value = trim($value, "'");
        $sql = sprintf("%s.naam LIKE '%%%s%%'", $targetTableAlias, $value);
        $sql .= sprintf(' OR %s.cqranking_id = %d', $targetTableAlias, $value);
        return $sql;
    }
}
