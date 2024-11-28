<?php

namespace App\Services;

class AirtimeService
{
    const PROVIDERS = [
        'MTN' => ['prefix' => ['0803', '0806', '0813', '0816', '0814', '0810', '0815', '0805', '0907', '0815']],
        'AIRTEL' => ['prefix' => ['0802', '0808', '0812', '0708', '0701', '0907']],
        '9MOBILE' => ['prefix' => ['0809', '0818', '0815', '0805', '0807', '0902', '0901', '0904']]
    ];

    public function validatePhoneNumberFormat(string $phoneNumber): bool
    {
        $cleanNumber = preg_replace('/\D/', '', $phoneNumber);
        return (strlen($cleanNumber) === 10 || strlen($cleanNumber) === 11);
    }

    public function validatePhoneNumberProvider(string $phoneNumber, string $provider): bool
    {
        $cleanNumber = preg_replace('/\D/', '', $phoneNumber);
        $fullNumber = strlen($cleanNumber) === 10 ? '0' . $cleanNumber : $cleanNumber;

        $providerPrefixes = self::PROVIDERS[strtoupper($provider)]['prefix'] ?? [];

        foreach ($providerPrefixes as $prefix) {
            if (strpos($fullNumber, $prefix) === 0) {
                return true;
            }
        }

        return false;
    }

    public function getSupportedProviders(): array
    {
        return array_keys(self::PROVIDERS);
    }
}