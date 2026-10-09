<?php declare(strict_types=1);

namespace App\Security;

use App\Entity\Ploeg;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Grants OWNER on a Ploeg to the User it is assigned to. Replaces the former ACL owner entries.
 *
 * @extends Voter<string, Ploeg>
 */
class PloegVoter extends Voter
{
    public const OWNER = 'OWNER';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return self::OWNER === $attribute && $subject instanceof Ploeg;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }
        $owner = $subject->getUser();
        return $owner instanceof User && $owner->getId() === $user->getId();
    }
}
