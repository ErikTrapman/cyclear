<?php

/*
 * This file is part of the Cyclear-game package.
 *
 * (c) Erik Trapman <veggatron@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Entity;

use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Translatable\Translatable;

use JMS\Serializer\Annotation as Serializer;

/**
 *
 * @ORM\Entity()
 * @ORM\Table(name="Country")
 * @Serializer\ExclusionPolicy("all")
 */
class Country implements Translatable
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @Serializer\Expose
     */
    private $id;

    /**
     *
     * @ORM\Column(type="string", length=2)
     */
    private $iso2; // varchar(2) NOT NULL default '',

    /**
     * @Gedmo\Translatable
     * @ORM\Column(nullable=true)
     * @Serializer\Expose
     */
    private $name;

    /**
     *
     * @Gedmo\Locale
     */
    private $locale;

    public function getId()
    {
        return $this->id;
    }

    public function getIso2()
    {
        return $this->iso2;
    }

    public function setIso2($iso2)
    {
        $this->iso2 = $iso2;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function setTranslatableLocale($locale)
    {
        $this->locale = $locale;
    }

    public function __toString()
    {
        return $this->getName();
    }
}