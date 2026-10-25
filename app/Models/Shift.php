<?php

namespace App\Models;

use App\Enums\ShiftStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shift extends Model
{
    protected $fillable = [
        'user_id',
        'status',
        'opening_cash',
        'expected_cash',
        'closing_cash',
        'variance',
        'orders_count',
        'sales_total',
        'discounts_total',
        'notes',
        'reviewed_by',
        'started_at',
        'ended_at',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => ShiftStatus::class,
            'opening_cash' => 'decimal:2',
            'expected_cash' => 'decimal:2',
            'closing_cash' => 'decimal:2',
            'variance' => 'decimal:2',
            'sales_total' => 'decimal:2',
            'discounts_total' => 'decimal:2',
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function isOpen(): bool
    {
        return $this->status === ShiftStatus::Open;
    }

    public function statusLabel(): string
    {
        return $this->status->label();
    }
}
