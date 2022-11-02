<?php declare(strict_types=1);

namespace App\Filter\Ploeg;

use Doctrine\ORM\Mapping\ClassMetaData;

class PloegUserFilter extends \Doctrine\ORM\Query\Filter\SQLFilter
{
    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias, $targetTable = '')
    {
        //if ($targetEntity->name != "Doctrine\Tests\Models\Company\CompanyPerson") {
        if ($targetEntity->name != "App\Entity\Ploeg") {
            return '';
        }
        return $targetTableAlias . '.user_id = ( SELECT u.id FROM User u WHERE u.username = ' . $this->getParameter('user') . ')';
    }
}
