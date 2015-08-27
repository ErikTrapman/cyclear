<?php

/*
 * This file is part of the Cyclear-game package.
 *
 * (c) Erik Trapman <veggatron@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Cyclear\GameBundle\Filter;



class RennerNaamFilter extends \Doctrine\ORM\Query\Filter\SQLFilter {

    
    public function addFilterConstraint(\Doctrine\ORM\Mapping\ClassMetadata $targetEntity, $targetTableAlias) {

        
        //if ($targetEntity->name != "Doctrine\Tests\Models\Company\CompanyPerson") {
        if ($targetEntity->name != "Cyclear\GameBundle\Entity\Renner") {
            return "";
        }
        
        $value = $this->getParameter('naam');
        $value = trim($value,"'");
        $sql = sprintf("%s.naam LIKE '%%%s%%'", $targetTableAlias, $value);
        $sql .= sprintf(' OR %s.cqranking_id = %d', $targetTableAlias, $value);
        return $sql;
    }

}

?>
