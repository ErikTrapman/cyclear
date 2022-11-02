<?php declare(strict_types=1);

namespace App\Filter;

class RennerNaamFilter extends \Doctrine\ORM\Query\Filter\SQLFilter
{
    public function addFilterConstraint(\Doctrine\ORM\Mapping\ClassMetadata $targetEntity, $targetTableAlias)
    {
        //if ($targetEntity->name != "Doctrine\Tests\Models\Company\CompanyPerson") {
        if ($targetEntity->name != "App\Entity\Renner") {
            return '';
        }

        $value = $this->getParameter('naam');
        $value = trim($value, "'");
        $sql = sprintf("%s.naam LIKE '%%%s%%'", $targetTableAlias, $value);
        $sql .= sprintf(' OR %s.cqranking_id = %d', $targetTableAlias, $value);
        return $sql;
    }
}
