<?php

namespace App\Security\Voter;

use App\Entity\AdminUser;
use App\Entity\AuditLog;
use App\Security\AuditVisibilityResolver;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class AuditLogVoter extends Voter
{
    public const string VIEW = 'AUDIT_LOG_VIEW';

    public function __construct(private readonly AuditVisibilityResolver $visibilityResolver) {}

    protected function supports(string $attribute, mixed $subject): bool
    {
        return self::VIEW === $attribute && $subject instanceof AuditLog;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        /** @var AuditLog $subject */
        $user = $token->getUser();

        if (!$user instanceof AdminUser) {
            return false;
        }

        return $this->visibilityResolver->isVisibleTo($subject->getEventType(), $user->getRole());
    }
}
