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

use Doctrine\ORM\Query\Filter\SQLFilter;
use Doctrine\ORM\Mapping\ClassMetaData;

class SeizoenFilter extends SQLFilter
{

    // TODO verplaatsen naar Ploeg map
    public function addFilterConstraint(ClassMetaData $targetEntity, $targetTableAlias)
    {
        if ($targetEntity->name == 'Cyclear\GameBundle\Entity\Ploeg') {
            return $targetTableAlias . '.seizoen_id = ( SELECT s.id FROM Seizoen s WHERE s.slug = ' . $this->getParameter('seizoen') . ')';
        }
        return "";
    }
}

?>
