<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Traits\AutoSortable;

class CafeTable extends Model
{
    use AutoSortable;

    protected $fillable = [
        'name',
        'number',
        'capacity',
        'area',
        'status',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function currentOrder(): HasOne
    {
        return $this->hasOne(Order::class)
            ->whereIn('status', ['open', 'saved'])
            ->latestOfMany();
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'occupied' => 'مشغولة',
            'awaiting_payment' => 'بانتظار الدفع',
            default => 'متاحة',
        };
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            'occupied' => 'red',
            'awaiting_payment' => 'amber',
            default => 'green',
        };
    }
}
