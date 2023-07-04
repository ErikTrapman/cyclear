<?php declare(strict_types=1);

namespace App\Filter\Ploeg;

use Doctrine\ORM\Mapping\ClassMetaData;
use Doctrine\ORM\Query\Filter\SQLFilter;

class PloegUserFilter extends SQLFilter
{
    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias, $targetTable = '')
    {
        if ('App\Entity\Ploeg' != $targetEntity->name) {
            return '';
        }
        return $targetTableAlias . '.user_id = ( SELECT u.id FROM User u WHERE u.username = ' . $this->getParameter('user') . ')';
    }
}
