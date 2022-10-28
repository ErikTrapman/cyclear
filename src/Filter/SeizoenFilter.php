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

use Doctrine\ORM\Mapping\ClassMetaData;
use Doctrine\ORM\Query\Filter\SQLFilter;

class SeizoenFilter extends SQLFilter
{
    // TODO verplaatsen naar Ploeg map
    public function addFilterConstraint(ClassMetaData $targetEntity, $targetTableAlias)
    {
        if ($targetEntity->name == 'App\Entity\Ploeg') {
            return $targetTableAlias . '.seizoen_id = ( SELECT s.id FROM Seizoen s WHERE s.slug = ' . $this->getParameter('seizoen') . ')';
        }
        return '';
    }
}
