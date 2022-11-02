<?php declare(strict_types=1);

namespace App\Filter\Ploeg;

use Doctrine\ORM\Mapping\ClassMetaData;

class PloegNaamFilter extends \Doctrine\ORM\Query\Filter\SQLFilter
{
    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias, $targetTable = '')
    {
        //if ($targetEntity->name != "Doctrine\Tests\Models\Company\CompanyPerson") {
        if ($targetEntity->name != "App\Entity\Ploeg") {
            return '';
        }
        return $targetTableAlias . '.naam LIKE \'%' . trim($this->getParameter('naam'), "'") . '%\'';
    }
}
