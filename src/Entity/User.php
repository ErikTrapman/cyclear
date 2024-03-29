<?php declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\LegacyPasswordAuthenticatedUserInterface;

/**
 * @ORM\Table(name="user")
 * @ORM\Entity()
 */
class User extends \FOS\UserBundle\Model\User implements \Serializable, LegacyPasswordAuthenticatedUserInterface
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\OneToMany(
     *  targetEntity="App\Entity\Ploeg", mappedBy="user"
     * )
     */
    private $ploeg;

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
}
