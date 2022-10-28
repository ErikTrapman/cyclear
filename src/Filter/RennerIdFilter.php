<?php declare(strict_types=1);

/*
 * This file is part of the Cyclear-game package.
 *
 * (c) Erik Trapman <veggatron@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Filter;

class RennerIdFilter extends \Doctrine\ORM\Query\Filter\SQLFilter
{
    public function addFilterConstraint(\Doctrine\ORM\Mapping\ClassMetadata $targetEntity, $targetTableAlias)
    {
        //if ($targetEntity->name != "Doctrine\Tests\Models\Company\CompanyPerson") {
        if ($targetEntity->name != "App\Entity\Renner") {
            return '';
        }
//        if($value instanceof \App\Entity\Renner){
//            throw new \UnexpectedValueException("filter expects an id, not an object");
//        }
        $value = $this->getParameter('renner');
        return sprintf('%s.renner = %d', $targetTableAlias, $value->getId());
    }
}
