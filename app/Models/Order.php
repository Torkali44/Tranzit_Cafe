<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    protected $fillable = [
        'order_number',
        'user_id',
        'shift_id',
        'cafe_table_id',
        'type',
        'status',
        'subtotal',
        'service_percent',
        'service_amount',
        'discount_type',
        'discount_value',
        'discount_amount',
        'total',
        'paid_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'service_percent' => 'decimal:2',
            'service_amount' => 'decimal:2',
            'discount_value' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function cafeTable(): BelongsTo
    {
        return $this->belongsTo(CafeTable::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    public function recalculate(): void
    {
        $subtotal = $this->items->sum('line_total');
        $serviceAmount = round($subtotal * ((float) $this->service_percent / 100), 2);

        $discountAmount = 0;
        if ($this->discount_type === 'percent') {
            $discountAmount = round($subtotal * ((float) $this->discount_value / 100), 2);
        } elseif ($this->discount_type === 'fixed') {
            $discountAmount = min((float) $this->discount_value, $subtotal + $serviceAmount);
        }

        $total = max(0, $subtotal + $serviceAmount - $discountAmount);

        $this->update([
            'subtotal' => $subtotal,
            'service_amount' => $serviceAmount,
            'discount_amount' => $discountAmount,
            'total' => $total,
        ]);
    }

    public static function nextOrderNumber(): int
    {
        return (int) (static::max('order_number') ?? 1000) + 1;
    }

    public function typeLabel(): string
    {
        return $this->type === 'dine_in' ? 'داخلي' : 'سفري';
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'saved' => 'محفوظ مؤقتاً',
            'paid' => 'مدفوع',
            'cancelled' => 'ملغي',
            default => 'مفتوح',
        };
    }
}
