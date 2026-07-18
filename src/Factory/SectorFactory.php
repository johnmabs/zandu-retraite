<?php

namespace App\Factory;

use App\Entity\Sector;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Sector>
 */
final class SectorFactory extends PersistentProxyObjectFactory
{
    public static function class(): string
    {
        return Sector::class;
    }

    protected function defaults(): array
    {
        return [
            'name' => self::faker()->unique()->jobTitle(),
            'code' => strtoupper(self::faker()->unique()->lexify('SECT???')),
            'isOther' => false,
        ];
    }
}
