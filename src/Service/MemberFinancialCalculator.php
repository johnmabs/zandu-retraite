<?php

namespace App\Service;

use App\Enum\SalaryCategory;
use App\Repository\SettingRepository;

final class MemberFinancialCalculator
{
    public function __construct(private readonly SettingRepository $settingRepository) {}

    // Détermine le palier (Bronze/Argent/Or/Platine) selon les seuils configurés
    public function resolveSalaryCategory(string $dailyPaymentAmount): SalaryCategory
    {
        $amount = (float) $dailyPaymentAmount;
        $thresholds = $this->settingRepository->getOrCreate()->getSalaryCategoryThresholds();

        foreach (SalaryCategory::cases() as $category) {
            $range = $thresholds[$category->value] ?? null;

            if (!$range) {
                continue;
            }

            $min = $range['min'];
            $max = $range['max']; // null = pas de plafond (palier le plus haut)

            if ($amount >= $min && (null === $max || $amount <= $max)) {
                return $category;
            }
        }

        // Filet de sécurité si les seuils configurés ont un trou (mauvaise config admin)
        return SalaryCategory::Bronze;
    }
}
