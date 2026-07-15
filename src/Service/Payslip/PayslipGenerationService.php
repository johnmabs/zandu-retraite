<?php

namespace App\Service\Payslip;

use App\Entity\AdminUser;
use App\Entity\Member;
use App\Entity\Payslip;
use App\Enum\AuditEventType;
use App\Enum\NotificationType;
use App\Repository\PaymentRepository;
use App\Repository\PayslipRepository;
use App\Service\DomainEventRecorder;
use Doctrine\ORM\EntityManagerInterface;

final class PayslipGenerationService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly PaymentRepository $paymentRepository,
        private readonly PayslipRepository $payslipRepository,
        private readonly PayslipCalculator $calculator,
        private readonly PayslipPdfGenerator $pdfGenerator,
        private readonly DomainEventRecorder $eventRecorder,
    ) {}

    public function generateForMember(Member $member, AdminUser $admin): Payslip
    {
        $periodStart = $this->resolvePeriodStart($member);
        $periodEnd = $periodStart->modify('+29 days'); // fenêtre de 30 jours inclusive

        $today = new \DateTimeImmutable('today');
        if ($periodEnd > $today) {
            throw new \DomainException(sprintf(
                'La période n\'est pas encore terminée (se termine le %s).',
                $periodEnd->format('d/m/Y'),
            ));
        }

        $payments = $this->paymentRepository->findConfirmedByMemberAndPeriod($member, $periodStart, $periodEnd);

        if ([] === $payments) {
            throw new \DomainException('Aucun versement confirmé sur cette période.');
        }

        $calculation = $this->calculator->calculate($member, $payments);

        $payslip = new Payslip();
        $payslip->setPayslipNumber(sprintf('BP-%s-%s', $member->getMemberNumber(), $periodEnd->format('Ymd')))
            ->setMember($member)
            ->setPeriodStart($periodStart)
            ->setPeriodEnd($periodEnd)
            ->setPaymentsCount($calculation->paymentsCount)
            ->setGrossAmount($calculation->grossAmount)
            ->setPensionShareAmount($calculation->pensionShareAmount)
            ->setManagementFeeAmount($calculation->managementFeeAmount)
            ->setCnssContributionAmount($calculation->cnssContributionAmount)
            ->setNetAmount($calculation->netAmount);

        $this->em->persist($payslip);
        $this->em->flush();

        $payslip->setPdfPath($this->pdfGenerator->generate($payslip));
        $this->em->flush();

        $this->eventRecorder->record(
            eventType: AuditEventType::PayslipGenerated,
            description: sprintf('%s a généré le bulletin %s pour %s', $admin->getFullName(), $payslip->getPayslipNumber(), $member->getFullName()),
            actorAdmin: $admin,
            actorMember: $member,
            notificationType: NotificationType::PayslipAvailable,
            notificationMessage: sprintf('Bulletin %s disponible pour %s.', $payslip->getPayslipNumber(), $member->getFullName()),
            notificationRelatedMember: $member,
        );

        return $payslip;
    }

    /** @param iterable<Member> $members */
    public function generateBatch(iterable $members, AdminUser $admin): PayslipBatchResult
    {
        $succeeded = [];
        $failed = [];

        foreach ($members as $member) {
            try {
                $succeeded[] = $this->generateForMember($member, $admin);
            } catch (\DomainException $e) {
                $failed[$member->getMemberNumber()] = $e->getMessage();
            }
        }

        return new PayslipBatchResult($succeeded, $failed);
    }

    private function resolvePeriodStart(Member $member): \DateTimeImmutable
    {
        $last = $this->payslipRepository->findLastForMember($member);

        return $last ? $last->getPeriodEnd()->modify('+1 day') : ($member->getRegisteredAt() ?? $member->getCreatedAt());
    }
}
