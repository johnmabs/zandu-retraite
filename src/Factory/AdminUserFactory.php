<?php

namespace App\Factory;

use App\Entity\AdminUser;
use App\Enum\AdminPermission;
use App\Enum\AdminRole;
use App\Enum\AdminStatus;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<AdminUser>
 */
final class AdminUserFactory extends PersistentObjectFactory
{
    public static function class(): string
    {
        return AdminUser::class;
    }

    protected function defaults(): array
    {
        return [
            'fullName' => self::faker()->name(),
            'login' => self::faker()->unique()->userName(),
            'pin' => password_hash('1234', PASSWORD_BCRYPT),
            'role' => AdminRole::SuperAdmin,
            'permissions' => AdminPermission::cases(), // instances brutes, setPermissions() fait ->value lui-même
            'status' => AdminStatus::Active,
        ];
    }
}
