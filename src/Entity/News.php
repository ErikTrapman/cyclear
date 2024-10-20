<?php declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'news')]
class News
{
    /**
     * @var int
     */
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Seizoen::class)]
    private $seizoen;

    #[ORM\Column]
    private $titel;

    #[ORM\Column(type: 'text')]
    private $content;

    public function getId(): int
    {
        return $this->id;
    }

    public function getSeizoen()
    {
        return $this->seizoen;
    }

    public function setSeizoen($seizoen): void
    {
        $this->seizoen = $seizoen;
    }

    public function getTitel()
    {
        return $this->titel;
    }

    public function setTitel($titel): void
    {
        $this->titel = $titel;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function setContent($content): void
    {
        $this->content = $content;
    }
}
