<?php

namespace App\Security\Voter;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;
// Import the Vote class
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

class UserVoter extends Voter
{
    public const VIEW_PROFILE = 'VIEW_PROFILE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === self::VIEW_PROFILE && $subject instanceof User;
    }

    // Add ?Vote $vote = null to the parameters
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }

        /** @var User $targetUser */
        $targetUser = $subject;

        return match ($attribute) {
            self::VIEW_PROFILE => $this->canView($targetUser, $user),
            default => false,
        };
    }

    private function canView(User $targetUser, UserInterface $currentUser): bool
    {
        if (in_array('ROLE_ADMIN', $currentUser->getRoles())) {
            return true;
        }

        return $currentUser->getUserIdentifier() === $targetUser->getUserIdentifier();
    }
}
