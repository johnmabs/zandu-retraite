<?php

namespace App\Service;

use App\Entity\AdminUser;
use App\Entity\AuditLog;
use App\Entity\Member;
use App\Entity\Notification;
use App\Enum\AuditEventType;
use App\Enum\NotificationType;
use App\Repository\AuditLogRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Point d'entrée unique pour tracer un événement métier : une entrée d'audit
 * immuable (AuditLog) et, optionnellement, une alerte visible par les admins
 * (Notification). Centralise ce qui était dupliqué entre
 * MemberRegistrationService::recordAudit()/notifyAdmins().
 */
final class DomainEventRecorder
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly AuditLogRepository $auditLogRepository,
        private readonly RequestStack $requestStack,
    ) {}

    public function record(
        AuditEventType $eventType,
        string $description,
        ?Member $actorMember = null,
        ?AdminUser $actorAdmin = null,
        ?array $context = null,
        ?NotificationType $notificationType = null,
        ?string $notificationMessage = null,
        ?Member $notificationRelatedMember = null,
    ): void {
        $auditLog = new AuditLog();
        $auditLog->setEventType($eventType)
            ->setDescription($description)
            ->setActorMember($actorMember)
            ->setActorAdmin($actorAdmin)
            ->setContext($context)
            ->setIpAddress($this->requestStack->getCurrentRequest()?->getClientIp());

        $this->auditLogRepository->record($auditLog);

        if ($notificationType) {
            $notification = new Notification();
            $notification->setType($notificationType)
                ->setMessage($notificationMessage ?? $description)
                ->setRelatedMember($notificationRelatedMember);

            $this->em->persist($notification);
            $this->em->flush();
        }
    }
}
