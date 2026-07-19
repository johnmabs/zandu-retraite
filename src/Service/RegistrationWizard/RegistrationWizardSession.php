<?php

namespace App\Service\RegistrationWizard;

use Symfony\Component\HttpFoundation\RequestStack;

final class RegistrationWizardSession
{
    private const string PREFIX = 'registration_wizard_';

    public function __construct(private readonly RequestStack $requestStack) {}

    public function get(string $step): ?array
    {
        return $this->requestStack->getSession()->get(self::PREFIX . $step);
    }

    public function set(string $step, array $data): void
    {
        $this->requestStack->getSession()->set(self::PREFIX . $step, $data);
    }

    public function hasStep(string $step): bool
    {
        return $this->requestStack->getSession()->has(self::PREFIX . $step);
    }

    public function clear(): void
    {
        $session = $this->requestStack->getSession();
        foreach (['step1', 'step2', 'step3'] as $step) {
            $session->remove(self::PREFIX . $step);
        }
    }
}
