<?php

namespace App\Entity;

use App\Enum\NotificationChannel;
use App\Repository\PayslipRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: PayslipRepository::class)]
#[ORM\Table(name: 'payslip')]
class Payslip
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private ?Uuid $id = null;

    // Numéro lisible, ex: BP-{memberNumber}-{yyyymmdd}
    #[ORM\Column(length: 40, unique: true)]
    private string $payslipNumber;

    #[ORM\ManyToOne(targetEntity: Member::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Member $member;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private \DateTimeImmutable $periodStart;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private \DateTimeImmutable $periodEnd;

    #[ORM\Column]
    private int $paymentsCount;

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2)]
    private string $grossAmount;

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2)]
    private string $pensionShareAmount;

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2)]
    private string $managementFeeAmount;

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2)]
    private string $cnssContributionAmount;

    // = pensionShareAmount, figé au moment de l'émission (voir note ci-dessous)
    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2)]
    private string $netAmount;

    #[ORM\Column(length: 20, enumType: NotificationChannel::class, nullable: true)]
    private ?NotificationChannel $sentVia = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $pdfPath = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $issuedAt;

    public function __construct()
    {
        $this->issuedAt = new \DateTimeImmutable();
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

    public function getNetAmount(): string
    {
        return $this->netAmount;
    }

    public function setNetAmount(string $netAmount): static
    {
        $this->netAmount = $netAmount;

        return $this;
    }

    // ... reste des getters/setters, même schéma

    public function getPayslipNumber(): ?string
    {
        return $this->payslipNumber;
    }

    public function setPayslipNumber(string $payslipNumber): static
    {
        $this->payslipNumber = $payslipNumber;

        return $this;
    }

    public function getPeriodStart(): ?\DateTimeImmutable
    {
        return $this->periodStart;
    }

    public function setPeriodStart(\DateTimeImmutable $periodStart): static
    {
        $this->periodStart = $periodStart;

        return $this;
    }

    public function getPeriodEnd(): ?\DateTimeImmutable
    {
        return $this->periodEnd;
    }

    public function setPeriodEnd(\DateTimeImmutable $periodEnd): static
    {
        $this->periodEnd = $periodEnd;

        return $this;
    }

    public function getPaymentsCount(): ?int
    {
        return $this->paymentsCount;
    }

    public function setPaymentsCount(int $paymentsCount): static
    {
        $this->paymentsCount = $paymentsCount;

        return $this;
    }

    public function getGrossAmount(): ?string
    {
        return $this->grossAmount;
    }

    public function setGrossAmount(string $grossAmount): static
    {
        $this->grossAmount = $grossAmount;

        return $this;
    }

    public function getPensionShareAmount(): ?string
    {
        return $this->pensionShareAmount;
    }

    public function setPensionShareAmount(string $pensionShareAmount): static
    {
        $this->pensionShareAmount = $pensionShareAmount;

        return $this;
    }

    public function getManagementFeeAmount(): ?string
    {
        return $this->managementFeeAmount;
    }

    public function setManagementFeeAmount(string $managementFeeAmount): static
    {
        $this->managementFeeAmount = $managementFeeAmount;

        return $this;
    }

    public function getCnssContributionAmount(): ?string
    {
        return $this->cnssContributionAmount;
    }

    public function setCnssContributionAmount(string $cnssContributionAmount): static
    {
        $this->cnssContributionAmount = $cnssContributionAmount;

        return $this;
    }

    public function getSentVia(): ?NotificationChannel
    {
        return $this->sentVia;
    }

    public function setSentVia(?NotificationChannel $sentVia): static
    {
        $this->sentVia = $sentVia;

        return $this;
    }

    public function getPdfPath(): ?string
    {
        return $this->pdfPath;
    }

    public function setPdfPath(?string $pdfPath): static
    {
        $this->pdfPath = $pdfPath;

        return $this;
    }

    public function getIssuedAt(): ?\DateTimeImmutable
    {
        return $this->issuedAt;
    }

    public function setIssuedAt(\DateTimeImmutable $issuedAt): static
    {
        $this->issuedAt = $issuedAt;

        return $this;
    }
}
