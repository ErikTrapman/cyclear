<?php declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * A trophy or badge. Normally won by a ploeg, so the user is derived from it and linking a ploeg to a user later
 * carries its awards along. Awards from before the seasons in this database (another edition of the game) have no
 * ploeg; they carry the user, season and team themselves.
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

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\Column(length: 32, nullable: true)]
    private ?string $season = null;

    #[ORM\Column(length: 64, nullable: true)]
    private ?string $team = null;

    public function __construct(
        #[ORM\ManyToOne(targetEntity: Ploeg::class)]
        #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
        private ?Ploeg $ploeg,
        #[ORM\Column(type: 'string', length: 32, enumType: AwardType::class)]
        private AwardType $type,
    ) {
    }

    public static function withoutPloeg(User $user, AwardType $type, string $season, string $team): self
    {
        $award = new self(null, $type);
        $award->user = $user;
        $award->season = $season;
        $award->team = $team;
        return $award;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getPloeg(): ?Ploeg
    {
        return $this->ploeg;
    }

    public function getType(): AwardType
    {
        return $this->type;
    }

    public function getUser(): ?User
    {
        return $this->ploeg?->getUser() ?? $this->user;
    }

    public function getSeason(): string
    {
        return $this->ploeg?->getSeizoen()->getIdentifier() ?? (string) $this->season;
    }

    public function getTeam(): string
    {
        return $this->ploeg?->getNaam() ?? (string) $this->team;
    }
}
