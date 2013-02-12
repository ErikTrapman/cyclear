<?php

namespace Cyclear\GameBundle\EntityManager;

use Cyclear\GameBundle\Entity\Renner;
use ErikTrapman\Bundle\CQRankingParserBundle\Nationality\NationalityResolver;

class RennerManager
{
    private $pattern = '[%d] %s';

    private $countrySuffix = '~ %s';

    private $countryLookupPattern = '%s ~ %s';

    private $em;
    
    private $nationalityResolver;

    public function __construct($em, NationalityResolver $nationalityResolver)
    {
        $this->em = $em;
        $this->nationalityResolver = $nationalityResolver;
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
    
    /**
     * 
     * @param type $rennerString
     * @return null
     */
    public function getCountryFromRennerSelectorTypeString($rennerString)
    {
        $rennerString = str_replace(' ', '', $rennerString);
        $rennerString = str_replace('~', ' ~ ', $rennerString);
        sscanf($rennerString, $this->countryLookupPattern,$renner,$countryAbbreviation);
        if (!strlen($countryAbbreviation)) {
            return null;
        }
        $fullName = $this->nationalityResolver->getFullNameFromCode($countryAbbreviation);
        $country = $this->em->getRepository("CyclearGameBundle:Country")->findOneByTranslation($fullName);
        return $country;
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