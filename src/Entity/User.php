<?php declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\LegacyPasswordAuthenticatedUserInterface;

#[ORM\Entity]
#[ORM\Table(name: 'user')]
class User extends \FOS\UserBundle\Model\User implements \Serializable, LegacyPasswordAuthenticatedUserInterface
{
    /**
     * @var int
     */
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected $id;

    #[ORM\OneToMany(targetEntity: Ploeg::class, mappedBy: 'user')]
    private $ploeg;

    #[ORM\Column(nullable: true)]
    private ?string $firstName = null;

    public function __construct()
    {
        parent::__construct();
        $this->ploeg = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    public function getPloeg()
    {
        return $this->ploeg;
    }

    public function setPloeg($ploeg): void
    {
        $this->ploeg = $ploeg;
    }

    public function getPloegBySeizoen($seizoen)
    {
        foreach ($this->getPloeg() as $ploeg) {
            if ($ploeg->getSeizoen() === $seizoen) {
                return $ploeg;
            }
        }
        return null;
    }

    public function setFirstName(?string $value = null): void
    {
        $this->firstName = $value;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }
}
