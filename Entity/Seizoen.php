<?php

namespace Cyclear\GameBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Seizoen {

    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column
     */
    private $identifier;
    
    /**
     *
     * @ORM\Column(type="boolean")
     */
    private $closed;

    public function getId() {
        return $this->id;
    }

    public function getIdentifier() {
        return $this->identifier;
    }

    public function setIdentifier($identifier) {
        $this->identifier = $identifier;
    }
    
    public function getClosed() {
        return $this->closed;
    }

    public function setClosed($closed) {
        $this->closed = $closed;
    }



}
