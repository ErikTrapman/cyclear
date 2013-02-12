<?php

namespace Cyclear\GameBundle\Entity;

class CountryRepository extends \Doctrine\ORM\EntityRepository
{

    public function findOneByTranslation($countryName, $locale = 'en_GB')
    {
        $transRepo = $this->_em->getRepository("Gedmo\\Translatable\\Entity\\Translation");
        $trans = $transRepo->findOneBy(array('content' => $countryName, 'locale' => $locale));
        if (null === $trans) {
            $country = $this->findOneByName($countryName);
        } else {
            $country = $this->find($trans->getForeignKey());
        }
        return $country;
    }
}