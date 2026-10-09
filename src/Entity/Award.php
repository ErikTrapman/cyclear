<?php declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * A trophy or badge. Normally won by a ploeg, so the user is derived from it and linking a ploeg to a user later
 * carries its awards along. Awards from before the seasons in this database (another edition of the game) have no
 * ploeg; they carry the user, season and team themselves.
 */
#[ORM\Entity(repositoryClass: \App\Repository\AwardRepository::class)]
#[ORM\Table(name: 'award')]
#[ORM\UniqueConstraint(name: 'award_ploeg_type_unique', columns: ['ploeg_id', 'type'])]
#[UniqueEntity(fields: ['ploeg', 'type'], message: 'Deze ploeg heeft deze award al.')]
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

    #[Assert\Callback]
    public function validatePloegOrDetails(ExecutionContextInterface $context): void
    {
        $hasDetails = null !== $this->user || null !== $this->season || null !== $this->team;
        if (null !== $this->ploeg && $hasDetails) {
            $context->buildViolation('Kies een ploeg óf vul speler, seizoen en ploegnaam in, niet allebei.')
                ->atPath('ploeg')->addViolation();
        } elseif (null === $this->ploeg && (null === $this->user || null === $this->season || null === $this->team)) {
            $context->buildViolation('Zonder ploeg zijn speler, seizoen en ploegnaam verplicht.')
                ->atPath('ploeg')->addViolation();
        }
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

    public function setPloeg(?Ploeg $ploeg): void
    {
        $this->ploeg = $ploeg;
    }

    public function setType(AwardType $type): void
    {
        $this->type = $type;
    }

    /**
     * The user stored on the award itself, only used when there is no ploeg.
     */
    public function getOwnUser(): ?User
    {
        return $this->user;
    }

    public function setOwnUser(?User $user): void
    {
        $this->user = $user;
    }

    public function getOwnSeason(): ?string
    {
        return $this->season;
    }

    public function setOwnSeason(?string $season): void
    {
        $this->season = $season;
    }

    public function getOwnTeam(): ?string
    {
        return $this->team;
    }

    public function setOwnTeam(?string $team): void
    {
        $this->team = $team;
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
