<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Cashier = 'cashier';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'أدمن — الإدارة',
            self::Cashier => 'كاشير',
        };
    }

    public function canAccessAdmin(): bool
    {
        return $this === self::Admin;
    }
}
