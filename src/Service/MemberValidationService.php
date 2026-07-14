<?php

namespace App\Service;

use App\Entity\AdminUser;
use App\Entity\Member;
use App\Enum\AuditEventType;
use App\Enum\MemberStatus;
use Doctrine\ORM\EntityManagerInterface;

final class MemberValidationService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly DomainEventRecorder $eventRecorder,
    ) {}

    public function approve(Member $member, AdminUser $admin): void
    {
        $this->assertPending($member);

        $member->setStatus(MemberStatus::Active);
        $member->setRegisteredAt(new \DateTimeImmutable());

        $this->em->flush();

        $this->eventRecorder->record(
            eventType: AuditEventType::MemberUpdated,
            description: sprintf('%s a validé l\'inscription de %s', $admin->getFullName(), $member->getFullName()),
            actorAdmin: $admin,
            actorMember: $member,
        );
    }

    public function reject(Member $member, AdminUser $admin, ?string $reason = null): void
    {
        $this->assertPending($member);

        $member->setStatus(MemberStatus::Rejected);

        $this->em->flush();

        $this->eventRecorder->record(
            eventType: AuditEventType::MemberUpdated,
            description: sprintf(
                '%s a rejeté l\'inscription de %s%s',
                $admin->getFullName(),
                $member->getFullName(),
                $reason ? " ($reason)" : '',
            ),
            actorAdmin: $admin,
            actorMember: $member,
        );
    }

    private function assertPending(Member $member): void
    {
        if (MemberStatus::Pending !== $member->getStatus()) {
            throw new \LogicException('Cette inscription a déjà été traitée.');
        }
    }
}
