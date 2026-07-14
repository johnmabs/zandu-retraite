<?php

namespace App\Entity;

use App\Enum\PaymentConfirmationMethod;
use App\Enum\PaymentMethod;
use App\Enum\PaymentSource;
use App\Enum\PaymentStatus;
use App\Repository\PaymentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: PaymentRepository::class)]
#[ORM\Table(name: 'payment')]
class Payment
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private ?Uuid $id = null;

    #[ORM\ManyToOne(targetEntity: Member::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Member $member;

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2)]
    #[Assert\Positive]
    private string $amount;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private \DateTimeImmutable $paymentDate;

    #[ORM\Column(length: 20, enumType: PaymentMethod::class)]
    private PaymentMethod $paymentMethod;

    // Qui a initié la saisie : le client lui-même ou un admin
    #[ORM\Column(length: 20, enumType: PaymentSource::class)]
    private PaymentSource $source;

    // Comment le versement a été confirmé
    #[ORM\Column(length: 20, enumType: PaymentConfirmationMethod::class)]
    private PaymentConfirmationMethod $confirmationMethod;

    #[ORM\Column(length: 20, enumType: PaymentStatus::class)]
    private PaymentStatus $status;

    // Numéro utilisé pour l'envoi MoMo/Airtel (nul si espèces/virement)
    #[ORM\Column(length: 20, nullable: true)]
    private ?string $senderPhoneNumber = null;

    // Référence retournée par l'API MoMo/Airtel ou numéro de virement
    #[ORM\Column(length: 100, nullable: true)]
    private ?string $externalReference = null;

    // Admin ayant enregistré/validé le versement (nul si confirmation 100% automatique)
    #[ORM\ManyToOne(targetEntity: AdminUser::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?AdminUser $recordedBy = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->status = PaymentStatus::Pending;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getMember(): Member
    {
        return $this->member;
    }

    public function setMember(Member $member): static
    {
        $this->member = $member;

        return $this;
    }

    public function getAmount(): string
    {
        return $this->amount;
    }

    public function setAmount(string $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getPaymentMethod(): PaymentMethod
    {
        return $this->paymentMethod;
    }

    public function setPaymentMethod(PaymentMethod $paymentMethod): static
    {
        $this->paymentMethod = $paymentMethod;

        return $this;
    }

    public function getSource(): PaymentSource
    {
        return $this->source;
    }

    public function setSource(PaymentSource $source): static
    {
        $this->source = $source;

        return $this;
    }

    public function getConfirmationMethod(): PaymentConfirmationMethod
    {
        return $this->confirmationMethod;
    }

    public function setConfirmationMethod(PaymentConfirmationMethod $confirmationMethod): static
    {
        $this->confirmationMethod = $confirmationMethod;

        return $this;
    }

    public function getStatus(): PaymentStatus
    {
        return $this->status;
    }

    public function setStatus(PaymentStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getRecordedBy(): ?AdminUser
    {
        return $this->recordedBy;
    }

    public function setRecordedBy(?AdminUser $recordedBy): static
    {
        $this->recordedBy = $recordedBy;

        return $this;
    }

    public function getPaymentDate(): ?\DateTimeImmutable
    {
        return $this->paymentDate;
    }

    public function setPaymentDate(\DateTimeImmutable $paymentDate): static
    {
        $this->paymentDate = $paymentDate;

        return $this;
    }

    public function getSenderPhoneNumber(): ?string
    {
        return $this->senderPhoneNumber;
    }

    public function setSenderPhoneNumber(?string $senderPhoneNumber): static
    {
        $this->senderPhoneNumber = $senderPhoneNumber;

        return $this;
    }

    public function getExternalReference(): ?string
    {
        return $this->externalReference;
    }

    public function setExternalReference(?string $externalReference): static
    {
        $this->externalReference = $externalReference;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    #[Assert\Callback]
    public function validateMethodConsistency(ExecutionContextInterface $context): void
    {
        if (PaymentMethod::Cash === $this->paymentMethod) {
            if (PaymentSource::AdminRecorded !== $this->source) {
                $context->buildViolation('Un paiement en espèces doit être saisi par un administrateur.')
                    ->atPath('source')
                    ->addViolation();
            }

            if (PaymentConfirmationMethod::ManualReview !== $this->confirmationMethod) {
                $context->buildViolation('Un paiement en espèces se confirme manuellement.')
                    ->atPath('confirmationMethod')
                    ->addViolation();
            }
        }

        if (PaymentSource::MemberDeclared === $this->source && PaymentMethod::Cash === $this->paymentMethod) {
            $context->buildViolation('Un membre ne peut pas déclarer un paiement en espèces lui-même.')
                ->atPath('paymentMethod')
                ->addViolation();
        }
    }
}
