<?php declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * A trophy or badge won by a ploeg. The user is derived from the ploeg, so linking a ploeg to a user later carries its awards along.
 */
#[ORM\Entity(repositoryClass: \App\Repository\AwardRepository::class)]
#[ORM\Table(name: 'award')]
#[ORM\UniqueConstraint(name: 'award_ploeg_type_unique', columns: ['ploeg_id', 'type'])]
class Award
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private int $id;

    public function __construct(
        #[ORM\ManyToOne(targetEntity: Ploeg::class)]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private Ploeg $ploeg,
        #[ORM\Column(type: 'string', length: 32, enumType: AwardType::class)]
        private AwardType $type,
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getPloeg(): Ploeg
    {
        return $this->ploeg;
    }

    public function getType(): AwardType
    {
        return $this->type;
    }
}
