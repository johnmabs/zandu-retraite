<?php

namespace App\Tests\Unit\Security;

use App\Enum\AdminRole;
use App\Enum\AuditEventType;
use App\Security\AuditVisibilityResolver;
use PHPUnit\Framework\TestCase;

class AuditVisibilityResolverTest extends TestCase
{
    private AuditVisibilityResolver $resolver;

    protected function setUp(): void
    {
        $this->resolver = new AuditVisibilityResolver();
    }

    public function testSuperAdminSeesRemoteAccess(): void
    {
        self::assertTrue($this->resolver->isVisibleTo(AuditEventType::RemoteAccess, AdminRole::SuperAdmin));
    }

    public function testCashierCannotSeeRemoteAccess(): void
    {
        self::assertFalse($this->resolver->isVisibleTo(AuditEventType::RemoteAccess, AdminRole::Cashier));
    }

    public function testSupervisorCannotSeeAdminUserUpdates(): void
    {
        $visibleToSupervisor = $this->resolver->visibleTypesFor(AdminRole::Supervisor);

        self::assertNotContains(AuditEventType::AdminUserUpdated, $visibleToSupervisor);
    }

    public function testVisibleTypesForSuperAdminIncludesEveryCase(): void
    {
        $visible = $this->resolver->visibleTypesFor(AdminRole::SuperAdmin);

        foreach (AuditEventType::cases() as $case) {
            self::assertContains($case, $visible, sprintf('%s devrait être visible pour SuperAdmin', $case->name));
        }
    }
}
