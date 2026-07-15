<?php

namespace App\Service\Payslip;

use App\Entity\Member;
use App\Repository\SettingRepository;

/**
 * Calcul pur, sans effet de bord — facilement testable unitairement sans
 * base de données (contrairement au reste du module qui persiste/génère
 * des fichiers). Utilise bcmath pour préserver la précision décimale sur
 * des montants FCFA potentiellement élevés une fois cumulés.
 */
final class PayslipCalculator
{
    public function __construct(private readonly SettingRepository $settingRepository) {}

    /** @return array{pension: string, management: string, cnss: string} */
    public function resolveRates(Member $member): array
    {
        $setting = $this->settingRepository->getOrCreate();

        return [
            'pension' => $member->getPensionRate() ?? $setting->getDefaultPensionRate(),
            'management' => $member->getManagementFeeRate() ?? $setting->getDefaultManagementFeeRate(),
            'cnss' => $member->getCnssRate() ?? $setting->getDefaultCnssRate(),
        ];
    }

    /** @param \App\Entity\Payment[] $payments */
    public function calculate(Member $member, array $payments): PayslipCalculation
    {
        $gross = '0.00';
        foreach ($payments as $payment) {
            $gross = bcadd($gross, $payment->getAmount(), 2);
        }

        $rates = $this->resolveRates($member);

        $pensionShare = bcdiv(bcmul($gross, $rates['pension'], 4), '100', 2);
        $managementFee = bcdiv(bcmul($gross, $rates['management'], 4), '100', 2);
        $cnssContribution = bcdiv(bcmul($gross, $rates['cnss'], 4), '100', 2);

        return new PayslipCalculation(
            grossAmount: $gross,
            pensionShareAmount: $pensionShare,
            managementFeeAmount: $managementFee,
            cnssContributionAmount: $cnssContribution,
            // Sémantique confirmée à l'étape 2.8 : le "net à percevoir" du
            // bulletin correspond à la part retraite, pas à un solde cash.
            netAmount: $pensionShare,
            paymentsCount: \count($payments),
        );
    }
}
