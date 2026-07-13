<?php

namespace App\Security\Voter;

use App\Entity\AdminUser;
use App\Enum\AdminPermission;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Usage: $this->denyAccessUnlessGranted(AdminPermission::ManageMembers);
 *
 * L'attribut est directement l'enum AdminPermission (Symfony 6.2+ accepte
 * des attributs non-string), pas besoin de le convertir en chaîne magique.
 */
class AdminPermissionVoter extends Voter
{
    protected function supports(string $attribute, mixed $subject): bool
    {
        return false; // jamais atteint : voir supportsAttribute() ci-dessous
    }

    public function supportsAttribute(mixed $attribute): bool
    {
        return $attribute instanceof AdminPermission;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        return false;
    }

    public function vote(TokenInterface $token, mixed $subject, array $attributes, ?Vote $vote = null): int
    {
        $user = $token->getUser();

        if (!$user instanceof AdminUser) {
            return self::ACCESS_ABSTAIN;
        }

        foreach ($attributes as $attribute) {
            if ($attribute instanceof AdminPermission && $user->hasPermission($attribute)) {
                return self::ACCESS_GRANTED;
            }
        }

        return self::ACCESS_DENIED;
    }
}
