<?php

namespace App\Helpers;

class Money
{
    public static function format(float|int|string|null $amount): string
    {
        return config('tranzit.currency').' '.number_format((float) $amount, 2);
    }
}
