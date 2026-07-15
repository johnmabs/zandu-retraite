<?php

namespace App\Service\Contract;

use App\Entity\Embeddable\Address;
use App\Entity\Member;
use App\Repository\SettingRepository;
use App\Service\MemberFinancialCalculator;
use App\Service\MemberRateResolver;

final class ContractPlaceholderResolver
{
    public function __construct(
        private readonly MemberRateResolver $rateResolver,
        private readonly MemberFinancialCalculator $financialCalculator,
        private readonly SettingRepository $settingRepository,
    ) {}

    public function resolve(string $templateBody, Member $member): string
    {
        $rates = $this->rateResolver->resolve($member);
        $setting = $this->settingRepository->getOrCreate();

        $replacements = [
            '{nom}' => $member->getLastName(),
            '{prenom}' => $member->getFirstName(),
            '{tel}' => $member->getPhone(),
            '{adresse}' => $this->formatAddress($member->getHomeAddress()),
            '{cni}' => $member->getIdDocumentNumber() ?? 'Non renseigné',
            '{id}' => $member->getMemberNumber(),
            '{date_inscription_fr}' => $member->getRegisteredAt()?->format('d/m/Y') ?? 'Non renseignée',
            '{duree}' => $member->getEngagementDuration() ? $member->getEngagementDuration()->value . ' ans' : 'Non renseignée',
            '{versJour}' => $member->getDailyPaymentAmount() . ' FCFA',
            '{versMensuel}' => bcmul($member->getDailyPaymentAmount(), '30', 2) . ' FCFA',
            '{taux_retraite}' => $rates['pension'],
            '{taux_gestion}' => $rates['management'],
            '{taux_cnss}' => $rates['cnss'],
            '{frais_inscription}' => ($member->getRegistrationFeeAmount() ?? $setting->getRegistrationFeeAmount()) . ' FCFA',
            '{capital_estime}' => $this->financialCalculator->estimateCapital($member) . ' FCFA',
            '{categorie}' => $member->getSalaryCategory()->value,
        ];

        return strtr($templateBody, $replacements);
    }

    private function formatAddress(Address $address): string
    {
        $parts = array_filter([$address->number, $address->street, $address->quarter, $address->commune, $address->department]);

        return $parts ? implode(', ', $parts) : 'Non renseignée';
    }
}
