<?php

namespace App\Security;

use App\Enum\AdminRole;
use App\Enum\AuditEventType;

/**
 * Reprend la matrice "quel rôle voit quel type d'événement" du prototype
 * (ALARM_TYPES[...].roles). Volontairement une classe statique de référence
 * plutôt qu'une table éditable : dans le proto, cette matrice n'était pas
 * modifiable depuis l'UI, seulement les DROITS d'admin le sont.
 */
final class AuditVisibilityResolver
{
    private const array VISIBILITY = [
        AuditEventType::MemberRegistered->value => [
            AdminRole::SuperAdmin,
            AdminRole::Manager,
            AdminRole::Cashier,
            AdminRole::Supervisor,
        ],
        AuditEventType::MemberUpdated->value => [AdminRole::SuperAdmin, AdminRole::Manager],
        AuditEventType::MemberDeleted->value => [AdminRole::SuperAdmin],

        AuditEventType::PaymentRecorded->value => [AdminRole::SuperAdmin, AdminRole::Cashier, AdminRole::Supervisor],
        AuditEventType::PaymentFailed->value => [AdminRole::SuperAdmin, AdminRole::Cashier],
        AuditEventType::PaymentDeleted->value => [AdminRole::SuperAdmin],

        AuditEventType::PayslipGenerated->value => [AdminRole::SuperAdmin, AdminRole::Cashier, AdminRole::Supervisor],

        AuditEventType::MemberLogin->value => [AdminRole::SuperAdmin, AdminRole::Supervisor],
        AuditEventType::MemberLoginFailed->value => [AdminRole::SuperAdmin, AdminRole::Supervisor],
        AuditEventType::AdminLogin->value => [AdminRole::SuperAdmin],
        AuditEventType::AdminLoginFailed->value => [AdminRole::SuperAdmin],
        AuditEventType::RemoteAccess->value => [AdminRole::SuperAdmin],

        AuditEventType::AdminUserUpdated->value => [AdminRole::SuperAdmin],
        AuditEventType::SettingsUpdated->value => [AdminRole::SuperAdmin, AdminRole::Supervisor],
        AuditEventType::RecordDeleted->value => [AdminRole::SuperAdmin],

        AuditEventType::MessageSent->value => [AdminRole::SuperAdmin, AdminRole::Manager],
        AuditEventType::ChatMessageReceived->value => [
            AdminRole::SuperAdmin,
            AdminRole::Manager,
            AdminRole::Cashier,
            AdminRole::Supervisor,
        ],

        AuditEventType::ApiCallSucceeded->value => [AdminRole::SuperAdmin, AdminRole::Cashier],
        AuditEventType::ApiCallFailed->value => [AdminRole::SuperAdmin, AdminRole::Cashier],
        AuditEventType::ContractIssued->value => [AdminRole::SuperAdmin, AdminRole::Manager],
    ];

    public function isVisibleTo(AuditEventType $eventType, AdminRole $role): bool
    {
        $allowedRoles = self::VISIBILITY[$eventType->value] ?? [AdminRole::SuperAdmin];

        return \in_array($role, $allowedRoles, true);
    }

    /** @return AuditEventType[] types visibles pour ce rôle, utile pour filtrer une requête de liste */
    public function visibleTypesFor(AdminRole $role): array
    {
        return array_values(array_filter(
            AuditEventType::cases(),
            fn(AuditEventType $type) => $this->isVisibleTo($type, $role),
        ));
    }
}
