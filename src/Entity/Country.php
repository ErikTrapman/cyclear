<?php declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Translatable\Translatable;

/**
 * @ORM\Entity()
 * @ORM\Table(name="country")
 */
class Country implements Translatable
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=2)
     */
    private $iso2; // varchar(2) NOT NULL default '',

    /**
     * @Gedmo\Translatable
     * @ORM\Column(nullable=true)
     */
    private $name;

    /**
     * @Gedmo\Locale
     */
    private $locale;

    public function getId(): int
    {
        return $this->id;
    }

    public function getIso2()
    {
        return $this->iso2;
    }

    public function setIso2($iso2): void
    {
        $this->iso2 = $iso2;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name): void
    {
        $this->name = $name;
    }

    public function setTranslatableLocale(string $locale): void
    {
        $this->locale = $locale;
    }

    public function __toString()
    {
        return $this->getName();
    }
}
