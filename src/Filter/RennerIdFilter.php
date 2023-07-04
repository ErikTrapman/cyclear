<?php declare(strict_types=1);

namespace App\Filter;

class RennerIdFilter extends \Doctrine\ORM\Query\Filter\SQLFilter
{
    public function addFilterConstraint(\Doctrine\ORM\Mapping\ClassMetadata $targetEntity, $targetTableAlias)
    {
        // if ($targetEntity->name != "Doctrine\Tests\Models\Company\CompanyPerson") {
        if ("App\Entity\Renner" != $targetEntity->name) {
            return '';
        }
//        if($value instanceof \App\Entity\Renner){
//            throw new \UnexpectedValueException("filter expects an id, not an object");
//        }
        $value = $this->getParameter('renner');
        return sprintf('%s.renner = %d', $targetTableAlias, $value->getId());
    }
}
