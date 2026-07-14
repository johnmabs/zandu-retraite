<?php

namespace App\Service;

use App\Entity\AuditLog;
use App\Entity\Member;
use App\Entity\Notification;
use App\Enum\AuditEventType;
use App\Enum\MemberStatus;
use App\Enum\NotificationType;
use App\Repository\AuditLogRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class MemberRegistrationService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly MemberNumberGenerator $memberNumberGenerator,
        private readonly MemberFinancialCalculator $financialCalculator,
        private readonly AuditLogRepository $auditLogRepository,
        private readonly RequestStack $requestStack,
    ) {}

    // $member arrive déjà peuplé par le formulaire, mais pas encore persisté ;
    // $plainPin est le PIN en clair issu du champ non-mappé du formulaire.
    public function register(Member $member, string $plainPin): Member
    {
        $member->setMemberNumber($this->memberNumberGenerator->generate());
        $member->setPin($this->passwordHasher->hashPassword($member, $plainPin));
        $member->setSalaryCategory($this->financialCalculator->resolveSalaryCategory($member->getDailyPaymentAmount()));
        $member->setStatus(MemberStatus::Pending);

        $this->em->persist($member);
        $this->em->flush();

        $this->recordAudit($member);
        $this->notifyAdmins($member);

        return $member;
    }

    private function recordAudit(Member $member): void
    {
        $auditLog = new AuditLog();
        $auditLog->setEventType(AuditEventType::MemberRegistered)
            ->setDescription(sprintf('Nouvelle inscription : %s (%s)', $member->getFullName(), $member->getMemberNumber()))
            ->setActorMember($member)
            ->setIpAddress($this->requestStack->getCurrentRequest()?->getClientIp());

        $this->auditLogRepository->record($auditLog);
    }

    private function notifyAdmins(Member $member): void
    {
        $notification = new Notification();
        $notification->setType(NotificationType::RegistrationSubmitted)
            ->setMessage(sprintf('%s vient de s\'inscrire et attend une validation.', $member->getFullName()))
            ->setRelatedMember($member);

        $this->em->persist($notification);
        $this->em->flush();
    }
}
