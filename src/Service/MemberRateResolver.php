<?php

namespace App\Service;

use App\Entity\Member;
use App\Repository\SettingRepository;

// Résolution des taux effectifs d'un membre (personnalisés, avec repli sur
// les valeurs par défaut de Setting) — utilisé par PayslipCalculator ET
// MemberFinancialCalculator, d'où son extraction en service autonome plutôt
// que la propriété exclusive de l'un ou l'autre.
final class MemberRateResolver
{
    public function __construct(private readonly SettingRepository $settingRepository) {}

    /** @return array{pension: string, management: string, cnss: string} */
    public function resolve(Member $member): array
    {
        $setting = $this->settingRepository->getOrCreate();

        return [
            'pension' => $member->getPensionRate() ?? $setting->getDefaultPensionRate(),
            'management' => $member->getManagementFeeRate() ?? $setting->getDefaultManagementFeeRate(),
            'cnss' => $member->getCnssRate() ?? $setting->getDefaultCnssRate(),
        ];
    }
}
