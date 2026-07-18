<?php

namespace App\Factory;

use App\Entity\Member;
use App\Enum\MemberStatus;
use App\Enum\SalaryCategory;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Member>
 */
final class MemberFactory extends PersistentProxyObjectFactory
{
    public static function class(): string
    {
        return Member::class;
    }

    protected function defaults(): array
    {
        return [
            'memberNumber' => 'MR-' . self::faker()->unique()->numerify('####'),
            'firstName' => self::faker()->firstName(),
            'lastName' => self::faker()->lastName(),
            'phone' => '+242' . self::faker()->unique()->numerify('#########'),
            'pin' => password_hash('1234', PASSWORD_BCRYPT),
            'sector' => SectorFactory::new(),
            'dailyPaymentAmount' => '1000.00',
            'status' => MemberStatus::Active,
            'salaryCategory' => SalaryCategory::Bronze,
            'registeredAt' => new \DateTimeImmutable('-60 days'),
        ];
    }

    public function pending(): static
    {
        return $this->with(['status' => MemberStatus::Pending, 'registeredAt' => null]);
    }
}
