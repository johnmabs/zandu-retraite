<?php

namespace App\Service\Payslip;

final readonly class PayslipCalculation
{
    public function __construct(
        public string $grossAmount,
        public string $pensionShareAmount,
        public string $managementFeeAmount,
        public string $cnssContributionAmount,
        public string $netAmount,
        public int $paymentsCount,
    ) {}
}
