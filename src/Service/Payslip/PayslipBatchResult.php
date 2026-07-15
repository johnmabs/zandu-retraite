<?php

namespace App\Service\Payslip;

use App\Entity\Payslip;

final readonly class PayslipBatchResult
{
    /**
     * @param Payslip[]              $succeeded
     * @param array<string, string>  $failed numéro de membre => raison de l'échec
     */
    public function __construct(
        public array $succeeded,
        public array $failed,
    ) {}
}
