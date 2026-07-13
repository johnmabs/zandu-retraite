<?php

namespace App\Security;

use App\Entity\AdminUser;
use App\Enum\AdminStatus;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class AdminUserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if ($user instanceof AdminUser && AdminStatus::Suspended === $user->getStatus()) {
            throw new CustomUserMessageAccountStatusException('Ce compte administrateur est suspendu.');
        }
    }

    public function checkPostAuth(UserInterface $user, ?TokenInterface $token = null): void {}
}
