<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function getValue(string $key, mixed $default = null): mixed
    {
        return Cache::remember("setting.{$key}", 60, function () use ($key, $default) {
            $row = static::query()->where('key', $key)->first();

            return $row?->value ?? $default;
        });
    }

    /** قراءة مباشرة من قاعدة البيانات بدون كاش (لنموذج الإعدادات) */
    public static function getFresh(string $key, mixed $default = null): mixed
    {
        $row = static::query()->where('key', $key)->first();

        return $row?->value ?? $default;
    }

    public static function setValue(string $key, mixed $value): void
    {
        static::query()->updateOrCreate(
            ['key' => $key],
            ['value' => is_bool($value) ? ($value ? '1' : '0') : (string) $value]
        );

        Cache::forget("setting.{$key}");
        // تأكيد مسح أي كاش قديم لنفس المفتاح عبر tags غير مستخدمة — نعيد الكتابة في الكاش بالقيمة الجديدة
        Cache::put("setting.{$key}", is_bool($value) ? ($value ? '1' : '0') : (string) $value, 60);
    }
}
