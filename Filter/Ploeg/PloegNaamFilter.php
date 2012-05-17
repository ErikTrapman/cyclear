<?php
namespace Cyclear\GameBundle\Filter\Ploeg;

use Doctrine\ORM\EntityManager,
    Doctrine\ORM\Mapping\ClassMetaData,
    Doctrine\ORM\Query\ParameterTypeInferer;

class PloegNaamFilter extends \Doctrine\ORM\Query\Filter\SQLFilter {

    
    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias, $targetTable = '') {

        
        //if ($targetEntity->name != "Doctrine\Tests\Models\Company\CompanyPerson") {
        if ($targetEntity->name != "Cyclear\GameBundle\Entity\Ploeg") {
            return "";
        }
        return $targetTableAlias . '.naam LIKE \'%' . trim($this->getParameter('naam'),"'" ) .'%\'';
    }

}

?>
