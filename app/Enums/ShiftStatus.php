<?php

namespace App\Enums;

enum ShiftStatus: string
{
    case Open = 'open';
    case Closed = 'closed';
    case Reviewed = 'reviewed';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'مفتوح',
            self::Closed => 'مغلق — بانتظار المراجعة',
            self::Reviewed => 'تمت المراجعة',
        };
    }
}
