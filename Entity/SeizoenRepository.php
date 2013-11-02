<?php

/*
 * This file is part of the Cyclear-game package.
 *
 * (c) Erik Trapman <veggatron@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cyclear\GameBundle\Entity;

use Doctrine\ORM\EntityRepository;

class SeizoenRepository extends EntityRepository
{

    public function getCurrent()
    {
        $qb = $this->createQueryBuilder("s")->where("s.current = 1");
        $res = $qb->getQuery()->getResult();
        if(array_key_exists(0, $res)){
            return $res[0];
        }
        return null;
    }
}
