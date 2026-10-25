<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WaitingList extends Model
{
    protected $fillable = [
        'customer_name',
        'party_size',
        'phone',
        'notes',
        'status',
        'cafe_table_id',
        'seated_at',
    ];

    protected function casts(): array
    {
        return [
            'seated_at' => 'datetime',
        ];
    }

    public function cafeTable(): BelongsTo
    {
        return $this->belongsTo(CafeTable::class);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'seated' => 'تم الجلوس',
            'cancelled' => 'ملغي',
            default => 'في الانتظار',
        };
    }
}
