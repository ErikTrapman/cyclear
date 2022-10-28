<?php declare(strict_types=1);

/*
 * This file is part of the Cyclear-game package.
 *
 * (c) Erik Trapman <veggatron@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="user")
 * @ORM\Entity()
 */
class User extends \FOS\UserBundle\Model\User implements \Serializable
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

    public function setPloeg($ploeg)
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
