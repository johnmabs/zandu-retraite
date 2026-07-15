<?php

namespace App\Service\Contract;

use App\Entity\AdminUser;
use App\Entity\ContractTemplate;
use App\Entity\IssuedContract;
use App\Entity\Member;
use App\Enum\AuditEventType;
use App\Enum\NotificationType;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\DomainEventRecorder;

final class ContractGenerationService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ContractPlaceholderResolver $resolver,
        private readonly ContractPdfGenerator $pdfGenerator,
        private readonly DomainEventRecorder $eventRecorder,
    ) {}

    public function issueForMember(Member $member, ContractTemplate $template, AdminUser $admin): IssuedContract
    {
        $resolvedBody = $this->resolver->resolve($template->getBody(), $member);

        $contract = new IssuedContract();
        $contract->setMember($member)
            ->setTemplate($template)
            ->setResolvedBody($resolvedBody);

        $this->em->persist($contract);
        $this->em->flush();

        $contract->setPdfPath($this->pdfGenerator->generate($contract));
        $this->em->flush();

        $this->eventRecorder->record(
            eventType: AuditEventType::ContractIssued,
            description: sprintf('%s a émis un contrat pour %s', $admin->getFullName(), $member->getFullName()),
            actorAdmin: $admin,
            actorMember: $member,
            notificationType: NotificationType::ContractIssued,
            notificationMessage: sprintf('Contrat émis pour %s, en attente de signature.', $member->getFullName()),
            notificationRelatedMember: $member,
        );

        return $contract;
    }

    public function sign(IssuedContract $contract): void
    {
        if ($contract->getSignedAt()) {
            throw new \LogicException('Ce contrat a déjà été signé.');
        }

        $contract->sign();
        $this->em->flush();
    }
}
