<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\AutoSortable;

class Item extends Model
{
    use AutoSortable;

    protected $fillable = [
        'category_id',
        'name',
        'price',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function addons(): HasMany
    {
        return $this->hasMany(ItemAddon::class);
    }

    public function activeAddons(): HasMany
    {
        return $this->addons()->where('is_active', true);
    }

    public function getAutoSortScope(): array
    {
        return ['category_id' => $this->category_id];
    }
}
