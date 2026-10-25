<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    protected $fillable = [
        'invoice_number',
        'order_id',
        'user_id',
        'shift_id',
        'cashier_name',
        'table_label',
        'order_type',
        'subtotal',
        'service_amount',
        'discount_amount',
        'total',
        'amount_paid',
        'change_amount',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'service_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total' => 'decimal:2',
            'amount_paid' => 'decimal:2',
            'change_amount' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public static function nextInvoiceNumber(): string
    {
        $last = static::orderByDesc('id')->value('invoice_number');
        $num = $last ? ((int) preg_replace('/\D/', '', $last)) + 1 : 1001;

        return 'INV-'.$num;
    }
}
