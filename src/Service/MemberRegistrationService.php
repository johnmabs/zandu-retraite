<?php

namespace App\Service;


use App\Entity\Member;
use App\Enum\AuditEventType;
use App\Enum\MemberStatus;
use App\Enum\NotificationType;
use App\Service\DomainEventRecorder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class MemberRegistrationService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly MemberNumberGenerator $memberNumberGenerator,
        private readonly MemberFinancialCalculator $financialCalculator,
        private readonly DomainEventRecorder $eventRecorder,
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

        $this->eventRecorder->record(
            eventType: AuditEventType::MemberRegistered,
            description: sprintf('Nouvelle inscription : %s (%s)', $member->getFullName(), $member->getMemberNumber()),
            actorMember: $member,
            notificationType: NotificationType::RegistrationSubmitted,
            notificationMessage: sprintf('%s vient de s\'inscrire et attend une validation.', $member->getFullName()),
            notificationRelatedMember: $member,
        );

        return $member;
    }
}
