<?php

namespace App\Service;

use App\Entity\Member;
use App\Enum\SalaryCategory;
use App\Repository\SettingRepository;
use App\Service\MemberRateResolverInterface;

final class MemberFinancialCalculator
{
    public function __construct(
        private readonly SettingRepository $settingRepository,
        private readonly MemberRateResolverInterface $memberRateResolver,
    ) {}

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

    // Projection simple et indicative (comme précisé dans le texte du contrat
    // lui-même) : montant journalier x 365 x durée d'engagement x part retraite.
    // Volontairement sans intérêts composés — une estimation plus fine
    // pourrait être ajoutée plus tard sans changer la signature de la méthode.
    public function estimateCapital(Member $member): string
    {
        $rates = $this->memberRateResolver->resolve($member);

        $years = $member->getEngagementDuration()?->value ?? 0;
        $totalContributed = bcmul($member->getDailyPaymentAmount(), (string) (365 * $years), 2);

        return bcdiv(bcmul($totalContributed, $rates['pension'], 4), '100', 2);
    }
}
