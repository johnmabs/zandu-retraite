<?php

namespace App\Service\Payslip;

use App\Entity\Member;
use App\Service\MemberRateResolver;

/**
 * Calcul pur, sans effet de bord — facilement testable unitairement sans
 * base de données (contrairement au reste du module qui persiste/génère
 * des fichiers). Utilise bcmath pour préserver la précision décimale sur
 * des montants FCFA potentiellement élevés une fois cumulés.
 */
final class PayslipCalculator
{
    public function __construct(private readonly MemberRateResolver $rateResolver) {}

    /** @param \App\Entity\Payment[] $payments */
    public function calculate(Member $member, array $payments): PayslipCalculation
    {
        $gross = '0.00';
        foreach ($payments as $payment) {
            $gross = bcadd($gross, $payment->getAmount(), 2);
        }

        $rates = $this->rateResolver->resolve($member);

        $pensionShare = bcdiv(bcmul($gross, $rates['pension'], 4), '100', 2);
        $managementFee = bcdiv(bcmul($gross, $rates['management'], 4), '100', 2);
        $cnssContribution = bcdiv(bcmul($gross, $rates['cnss'], 4), '100', 2);

        return new PayslipCalculation(
            grossAmount: $gross,
            pensionShareAmount: $pensionShare,
            managementFeeAmount: $managementFee,
            cnssContributionAmount: $cnssContribution,
            netAmount: $pensionShare,
            paymentsCount: \count($payments),
        );
    }
}
