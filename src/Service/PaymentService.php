<?php

namespace App\Service;

use App\Entity\AdminUser;
use App\Entity\Member;
use App\Entity\Payment;
use App\Enum\AuditEventType;
use App\Enum\NotificationType;
use App\Enum\PaymentConfirmationMethod;
use App\Enum\PaymentMethod;
use App\Enum\PaymentSource;
use App\Enum\PaymentStatus;
use Doctrine\ORM\EntityManagerInterface;

final class PaymentService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly DomainEventRecorder $eventRecorder,
    ) {}

    /**
     * Un membre déclare un versement MoMo/Airtel/Virement (jamais espèces).
     * MoMo/Airtel restent en Pending ici : la confirmation réelle vient de
     * l'API du fournisseur (intégration à câbler à l'étape Notifications /
     * intégrations externes, via Messenger pour ne pas bloquer la requête
     * HTTP le temps que l'API tierce réponde).
     */
    public function declareByMember(Member $member, Payment $payment): Payment
    {
        $payment->setMember($member);
        $payment->setSource(PaymentSource::MemberDeclared);
        $payment->setConfirmationMethod(
            PaymentMethod::BankTransfer === $payment->getPaymentMethod()
                ? PaymentConfirmationMethod::ManualReview
                : PaymentConfirmationMethod::ApiAuto,
        );
        $payment->setStatus(PaymentStatus::Pending);

        $this->em->persist($payment);
        $this->em->flush();

        $this->eventRecorder->record(
            eventType: AuditEventType::PaymentRecorded,
            description: sprintf('%s a déclaré un versement de %s FCFA (%s)', $member->getFullName(), $payment->getAmount(), $payment->getPaymentMethod()->value),
            actorMember: $member,
            notificationType: NotificationType::PaymentReceived,
            notificationMessage: sprintf('Nouveau versement déclaré par %s, en attente de confirmation.', $member->getFullName()),
            notificationRelatedMember: $member,
        );

        return $payment;
    }

    // Un admin saisit un encaissement en espèces : confirmé immédiatement (l'agent a le cash en main)
    public function recordCashByAdmin(Member $member, Payment $payment, AdminUser $admin): Payment
    {
        $payment->setMember($member);
        $payment->setPaymentMethod(PaymentMethod::Cash);
        $payment->setSource(PaymentSource::AdminRecorded);
        $payment->setConfirmationMethod(PaymentConfirmationMethod::ManualReview);
        $payment->setStatus(PaymentStatus::Confirmed);
        $payment->setRecordedBy($admin);

        $this->em->persist($payment);
        $this->em->flush();

        $this->eventRecorder->record(
            eventType: AuditEventType::PaymentRecorded,
            description: sprintf('%s a encaissé %s FCFA en espèces pour %s', $admin->getFullName(), $payment->getAmount(), $member->getFullName()),
            actorAdmin: $admin,
            notificationType: NotificationType::PaymentReceived,
            notificationMessage: sprintf('Versement espèces de %s FCFA enregistré pour %s.', $payment->getAmount(), $member->getFullName()),
            notificationRelatedMember: $member,
        );

        return $payment;
    }

    // Un admin valide ou rejette un versement en attente de revue manuelle (virements essentiellement)
    public function reviewManualPayment(Payment $payment, AdminUser $admin, bool $approve, ?string $note = null): Payment
    {
        if (PaymentConfirmationMethod::ManualReview !== $payment->getConfirmationMethod()) {
            throw new \LogicException('Ce versement ne relève pas d\'une revue manuelle.');
        }

        if (PaymentStatus::Pending !== $payment->getStatus()) {
            throw new \LogicException('Ce versement a déjà été traité.');
        }

        $payment->setStatus($approve ? PaymentStatus::Confirmed : PaymentStatus::Rejected);
        $payment->setRecordedBy($admin);

        $this->em->flush();

        $this->eventRecorder->record(
            eventType: $approve ? AuditEventType::PaymentRecorded : AuditEventType::PaymentFailed,
            description: sprintf(
                '%s a %s le versement de %s FCFA de %s%s',
                $admin->getFullName(),
                $approve ? 'validé' : 'rejeté',
                $payment->getAmount(),
                $payment->getMember()->getFullName(),
                $note ? " ($note)" : '',
            ),
            actorAdmin: $admin,
            notificationType: NotificationType::PaymentReceived,
            notificationMessage: $approve
                ? sprintf('Versement de %s confirmé.', $payment->getMember()->getFullName())
                : sprintf('Versement de %s rejeté.', $payment->getMember()->getFullName()),
            notificationRelatedMember: $payment->getMember(),
        );

        return $payment;
    }
}
