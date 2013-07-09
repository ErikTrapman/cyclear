<?php
namespace Cyclear\GameBundle\Filter;



class RennerIdFilter extends \Doctrine\ORM\Query\Filter\SQLFilter {

    
    public function addFilterConstraint(\Doctrine\ORM\Mapping\ClassMetadata $targetEntity, $targetTableAlias) {

        
        //if ($targetEntity->name != "Doctrine\Tests\Models\Company\CompanyPerson") {
        if ($targetEntity->name != "Cyclear\GameBundle\Entity\Renner") {
            return "";
        }
//        if($value instanceof \Cyclear\GameBundle\Entity\Renner){
//            throw new \UnexpectedValueException("filter expects an id, not an object");
//        }
        $value = $this->getParameter('renner');
        return sprintf("%s.renner = %d",$targetTableAlias, $value->getId());
    }
    

}
