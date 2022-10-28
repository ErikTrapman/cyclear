<?php declare(strict_types=1);

/*
 * This file is part of the Cyclear-game package.
 *
 * (c) Erik Trapman <veggatron@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
