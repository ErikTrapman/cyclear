<?php

/*
 * This file is part of the Cyclear-game package.
 *
 * (c) Erik Trapman <veggatron@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cyclear\GameBundle\EntityManager;

use Cyclear\GameBundle\Entity\Renner;

class RennerManager
{
    private $pattern = '[%d] %s';

    public function __construct()
    {
    }

    /**
     *
     * @param type $rennerString
     * @return Renner 
     */
    public function createRennerFromRennerSelectorTypeString($rennerString)
    {
        $cqId = $this->getCqIdFromRennerSelectorTypeString($rennerString);
        $renner = new Renner();
        $renner->setNaam($this->getNameFromRennerSelectorTypeString($rennerString, $cqId));
        $renner->setCqRanking_id($cqId);
        return $renner;
    }
    
    public function getRennerSelectorTypeStringFromRenner(Renner $renner)
    {
        return sprintf($this->pattern, $renner->getCqRankingId(), $renner->getNaam());
    }

    public function getCqIdFromRennerSelectorTypeString($string)
    {
        sscanf($string, "[%d]", $cqId);
        return $cqId;
    }

    public function getNameFromRennerSelectorTypeString($string, $cqId = null)
    {
        if (null === $cqId) {
            $cqId = $this->getCqIdFromRennerSelectorTypeString($string);
        }
        return trim(str_replace(sprintf('[%d]', $cqId), '', $string));
    }

    public function getRennerSelectorTypeString($cqRankingId, $name)
    {
        return sprintf($this->pattern, $cqRankingId, $name);
    }
}