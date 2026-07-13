<?php

namespace App\Entity;

use App\Enum\ApiEnvironment;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

// Singleton : une seule ligne en base, gérée par SettingRepository::getOrCreate()
#[ORM\Entity]
#[ORM\Table(name: 'setting')]
class Setting
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private ?Uuid $id = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2)]
    private string $defaultPensionRate = '70.00';

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2)]
    private string $defaultManagementFeeRate = '15.00';

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2)]
    private string $defaultCnssRate = '10.00';

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2)]
    private string $registrationFeeAmount = '5000.00';

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $mtnMomoNumber = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $airtelMoneyNumber = null;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $bankIban = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $bankName = null;

    // Environnement actif par intégration — les clés vivent dans le Vault, pas ici
    #[ORM\Column(length: 20, enumType: ApiEnvironment::class)]
    private ApiEnvironment $mtnApiEnvironment = ApiEnvironment::Sandbox;

    #[ORM\Column(length: 20, enumType: ApiEnvironment::class)]
    private ApiEnvironment $airtelApiEnvironment = ApiEnvironment::Sandbox;

    #[ORM\Column(length: 20, enumType: ApiEnvironment::class)]
    private ApiEnvironment $cnssApiEnvironment = ApiEnvironment::Sandbox;

    // Seuils des paliers (Bronze/Argent/Or/Platine) — configurables plutôt qu'en dur
    #[ORM\Column(type: Types::JSON)]
    private array $salaryCategoryThresholds = [
        'bronze' => ['min' => 0, 'max' => 1999],
        'silver' => ['min' => 2000, 'max' => 4999],
        'gold' => ['min' => 5000, 'max' => 9999],
        'platinum' => ['min' => 10000, 'max' => null],
    ];

    #[ORM\Column]
    private bool $notifyAdminByEmail = true;

    #[ORM\Column]
    private bool $notifyAdminBySms = true;

    #[ORM\Column]
    private bool $notifyAdminByWhatsapp = true;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    // ... reste des getters/setters

    public function getDefaultPensionRate(): ?string
    {
        return $this->defaultPensionRate;
    }

    public function setDefaultPensionRate(string $defaultPensionRate): static
    {
        $this->defaultPensionRate = $defaultPensionRate;

        return $this;
    }

    public function getDefaultManagementFeeRate(): ?string
    {
        return $this->defaultManagementFeeRate;
    }

    public function setDefaultManagementFeeRate(string $defaultManagementFeeRate): static
    {
        $this->defaultManagementFeeRate = $defaultManagementFeeRate;

        return $this;
    }

    public function getDefaultCnssRate(): ?string
    {
        return $this->defaultCnssRate;
    }

    public function setDefaultCnssRate(string $defaultCnssRate): static
    {
        $this->defaultCnssRate = $defaultCnssRate;

        return $this;
    }

    public function getRegistrationFeeAmount(): ?string
    {
        return $this->registrationFeeAmount;
    }

    public function setRegistrationFeeAmount(string $registrationFeeAmount): static
    {
        $this->registrationFeeAmount = $registrationFeeAmount;

        return $this;
    }

    public function getMtnMomoNumber(): ?string
    {
        return $this->mtnMomoNumber;
    }

    public function setMtnMomoNumber(?string $mtnMomoNumber): static
    {
        $this->mtnMomoNumber = $mtnMomoNumber;

        return $this;
    }

    public function getAirtelMoneyNumber(): ?string
    {
        return $this->airtelMoneyNumber;
    }

    public function setAirtelMoneyNumber(?string $airtelMoneyNumber): static
    {
        $this->airtelMoneyNumber = $airtelMoneyNumber;

        return $this;
    }

    public function getBankIban(): ?string
    {
        return $this->bankIban;
    }

    public function setBankIban(?string $bankIban): static
    {
        $this->bankIban = $bankIban;

        return $this;
    }

    public function getBankName(): ?string
    {
        return $this->bankName;
    }

    public function setBankName(?string $bankName): static
    {
        $this->bankName = $bankName;

        return $this;
    }

    public function getMtnApiEnvironment(): ?ApiEnvironment
    {
        return $this->mtnApiEnvironment;
    }

    public function setMtnApiEnvironment(ApiEnvironment $mtnApiEnvironment): static
    {
        $this->mtnApiEnvironment = $mtnApiEnvironment;

        return $this;
    }

    public function getAirtelApiEnvironment(): ?ApiEnvironment
    {
        return $this->airtelApiEnvironment;
    }

    public function setAirtelApiEnvironment(ApiEnvironment $airtelApiEnvironment): static
    {
        $this->airtelApiEnvironment = $airtelApiEnvironment;

        return $this;
    }

    public function getCnssApiEnvironment(): ?ApiEnvironment
    {
        return $this->cnssApiEnvironment;
    }

    public function setCnssApiEnvironment(ApiEnvironment $cnssApiEnvironment): static
    {
        $this->cnssApiEnvironment = $cnssApiEnvironment;

        return $this;
    }

    public function getSalaryCategoryThresholds(): array
    {
        return $this->salaryCategoryThresholds;
    }

    public function setSalaryCategoryThresholds(array $salaryCategoryThresholds): static
    {
        $this->salaryCategoryThresholds = $salaryCategoryThresholds;

        return $this;
    }

    public function isNotifyAdminByEmail(): ?bool
    {
        return $this->notifyAdminByEmail;
    }

    public function setNotifyAdminByEmail(bool $notifyAdminByEmail): static
    {
        $this->notifyAdminByEmail = $notifyAdminByEmail;

        return $this;
    }

    public function isNotifyAdminBySms(): ?bool
    {
        return $this->notifyAdminBySms;
    }

    public function setNotifyAdminBySms(bool $notifyAdminBySms): static
    {
        $this->notifyAdminBySms = $notifyAdminBySms;

        return $this;
    }

    public function isNotifyAdminByWhatsapp(): ?bool
    {
        return $this->notifyAdminByWhatsapp;
    }

    public function setNotifyAdminByWhatsapp(bool $notifyAdminByWhatsapp): static
    {
        $this->notifyAdminByWhatsapp = $notifyAdminByWhatsapp;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
