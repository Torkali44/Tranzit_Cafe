<?php

namespace App\Services;

use App\Models\Setting;

class SettingsService
{
    public function servicePercent(): float
    {
        return (float) Setting::getValue('service_percent', config('tranzit.service_percent', 12));
    }

    public function cafeName(): string
    {
        return (string) Setting::getValue('cafe_name', config('tranzit.name', 'Tranzit'));
    }

    public function cafeNameAr(): string
    {
        return (string) Setting::getValue('cafe_name_ar', config('tranzit.name_ar', 'ترانزيت'));
    }

    public function update(array $data): void
    {
        $map = [
            'service_percent' => $data['service_percent'] ?? null,
            'cafe_name' => $data['cafe_name'] ?? null,
            'cafe_name_ar' => $data['cafe_name_ar'] ?? null,
        ];

        foreach ($map as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }
            Setting::setValue($key, $value);
        }
    }

    public function all(): array
    {
        // قراءة طازجة بدون كاش حتى يظهر الاسم فور الحفظ
        return [
            'service_percent' => (float) Setting::getFresh('service_percent', config('tranzit.service_percent', 12)),
            'cafe_name' => (string) Setting::getFresh('cafe_name', config('tranzit.name', 'Tranzit')),
            'cafe_name_ar' => (string) Setting::getFresh('cafe_name_ar', config('tranzit.name_ar', 'ترانزيت')),
        ];
    }
}
