<?php

use App\Helpers\Money;
use App\Services\SettingsService;

if (! function_exists('money')) {
    function money(float|int|string|null $amount): string
    {
        return Money::format($amount);
    }
}

if (! function_exists('tranzit')) {
    function tranzit(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return config('tranzit');
        }

        try {
            $settings = app(SettingsService::class);

            return match ($key) {
                'service_percent' => $settings->servicePercent(),
                'name' => $settings->cafeName(),
                'name_ar' => $settings->cafeNameAr(),
                default => config('tranzit.'.$key, $default),
            };
        } catch (Throwable) {
            return config('tranzit.'.$key, $default);
        }
    }
}
