<?php
namespace Cyclear\GameBundle\Filter;

class RennerNaamFilter extends \Doctrine\ORM\Query\Filter\SQLFilter {

    
    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias, $targetTable = '') {

        
        //if ($targetEntity->name != "Doctrine\Tests\Models\Company\CompanyPerson") {
        if ($targetEntity->name != "Cyclear\GameBundle\Entity\Renner") {
            return "";
        }

        return $targetTableAlias . '.naam LIKE ' . $this->getParameter('naam');
    }

}

?>
