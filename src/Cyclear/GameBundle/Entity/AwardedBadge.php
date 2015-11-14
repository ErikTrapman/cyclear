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

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class AwardedBadge
{

    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Cyclear\GameBundle\Entity\Badge")
     */
    private $badge;

    /**
     * @ORM\ManyToOne(targetEntity="Cyclear\GameBundle\Entity\User")
     */
    private $user;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $recurringAmount;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getBadge()
    {
        return $this->badge;
    }

    /**
     * @param mixed $badge
     */
    public function setBadge($badge)
    {
        $this->badge = $badge;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return mixed
     */
    public function getRecurringAmount()
    {
        return $this->recurringAmount;
    }

    /**
     * @param mixed $recurringAmount
     */
    public function setRecurringAmount($recurringAmount)
    {
        $this->recurringAmount = $recurringAmount;
    }


}