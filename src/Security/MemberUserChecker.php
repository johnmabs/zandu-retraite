<?php

namespace App\Security;

use App\Entity\Member;
use App\Enum\MemberStatus;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class MemberUserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof Member) {
            return;
        }

        match ($user->getStatus()) {
            MemberStatus::Pending => throw new CustomUserMessageAccountStatusException(
                'Votre inscription est en attente de validation par un administrateur.'
            ),
            MemberStatus::Suspended => throw new CustomUserMessageAccountStatusException(
                'Votre compte est suspendu. Contactez le support.'
            ),
            MemberStatus::Closed => throw new CustomUserMessageAccountStatusException(
                'Ce compte est clôturé.'
            ),
            MemberStatus::Active => null,
        };
    }

    public function checkPostAuth(UserInterface $user, ?TokenInterface $token = null): void
    {
        // rien de plus à vérifier après authentification réussie
    }
}
