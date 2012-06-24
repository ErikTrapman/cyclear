<?php

namespace Cyclear\GameBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Entity\User as BaseUser;

/**
 * Cyclear\GameBundle\Entity\User
 *
 * @ORM\Table(name="User")
 * @ORM\Entity
 */
class User extends BaseUser implements \Serializable
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * 
     * @ORM\OneToOne(targetEntity="Cyclear\GameBundle\Entity\Ploeg", mappedBy="user")
     */
    private $ploeg;
    
    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }
    
    public function serialize()
    {
        return serialize(array(
            $this->id,
            ));
    }

    public function unserialize($data)
    {
        list(
       $this->id
        ) = unserialize($data);
    }
    
    public function getPloeg() 
    {
        return $this->ploeg;
    }

    public function setPloeg($ploeg) 
    {
        $this->ploeg = $ploeg;
    }


    
}