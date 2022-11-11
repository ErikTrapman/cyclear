<?php declare(strict_types=1);

namespace App\EntityManager;

use App\Entity\Renner;

class RennerManager
{
    private string $pattern = '[%d] %s';

    public function createRennerFromRennerSelectorTypeString($rennerString): Renner
    {
        $cqId = $this->getCqIdFromRennerSelectorTypeString($rennerString);
        $renner = new Renner();
        $renner->setNaam($this->getNameFromRennerSelectorTypeString($rennerString, $cqId));
        $renner->setCqRanking_id($cqId);
        return $renner;
    }

    public function getRennerSelectorTypeStringFromRenner(Renner $renner): string
    {
        return sprintf($this->pattern, $renner->getCqRankingId(), $renner->getNaam());
    }

    public function getCqIdFromRennerSelectorTypeString(string $string): string
    {
        sscanf($string, '[%d]', $cqId);
        return (string) $cqId;
    }

    public function getNameFromRennerSelectorTypeString(string $string, $cqId = null): string
    {
        if (null === $cqId) {
            $cqId = $this->getCqIdFromRennerSelectorTypeString($string);
        }
        return trim(str_replace(sprintf('[%d]', $cqId), '', $string));
    }

    public function getRennerSelectorTypeString($cqRankingId, $name): string
    {
        return sprintf($this->pattern, $cqRankingId, $name);
    }
}
