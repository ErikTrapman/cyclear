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