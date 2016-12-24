<?php

/*
 * This file is part of the Cyclear-game package.
 *
 * (c) Erik Trapman <veggatron@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Cyclear\GameBundle\Filter\Ploeg;

use Doctrine\ORM\EntityManager,
    Doctrine\ORM\Mapping\ClassMetaData,
    Doctrine\ORM\Query\ParameterTypeInferer;

class PloegNaamFilter extends \Doctrine\ORM\Query\Filter\SQLFilter
{


    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias, $targetTable = '')
    {


        //if ($targetEntity->name != "Doctrine\Tests\Models\Company\CompanyPerson") {
        if ($targetEntity->name != "Cyclear\GameBundle\Entity\Ploeg") {
            return "";
        }
        return $targetTableAlias . '.naam LIKE \'%' . trim($this->getParameter('naam'), "'") . '%\'';
    }

}

?>
