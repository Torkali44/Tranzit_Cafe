<?php

namespace App\Traits;

trait AutoSortable
{
    protected static function bootAutoSortable()
    {
        static::saving(function ($model) {
            $scopeChanged = false;
            if (method_exists($model, 'getAutoSortScope')) {
                foreach (array_keys($model->getAutoSortScope()) as $col) {
                    if ($model->isDirty($col)) {
                        $scopeChanged = true;
                        break;
                    }
                }
            }

            if (($model->isDirty('sort_order') || $scopeChanged) && $model->sort_order !== null) {
                // If sort_order is changed, or moved to a new scope, we need to shift other records
                $query = static::where('sort_order', '>=', $model->sort_order);
                
                // Add scope if defined on the model
                if (method_exists($model, 'getAutoSortScope')) {
                    foreach ($model->getAutoSortScope() as $column => $value) {
                        $query->where($column, $value);
                    }
                }
                
                if ($model->exists && !$scopeChanged) {
                    $query->where('id', '!=', $model->id);
                    
                    // If moving down (e.g., 2 to 5), we should decrement items between 2 and 5
                    // If moving up (e.g., 5 to 2), we should increment items between 2 and 5
                    $oldSort = $model->getOriginal('sort_order');
                    $newSort = $model->sort_order;
                    
                    if ($oldSort !== null) {
                        if ($newSort > $oldSort) {
                            // Moving down: 2 -> 5. Items 3, 4, 5 become 2, 3, 4.
                            $q = static::whereBetween('sort_order', [$oldSort + 1, $newSort])
                                ->where('id', '!=', $model->id);
                            if (method_exists($model, 'getAutoSortScope')) {
                                foreach ($model->getAutoSortScope() as $col => $val) {
                                    $q->where($col, $val);
                                }
                            }
                            $q->decrement('sort_order');
                            return; // Handled
                        } else {
                            // Moving up: 5 -> 2. Items 2, 3, 4 become 3, 4, 5.
                            $q = static::whereBetween('sort_order', [$newSort, $oldSort - 1])
                                ->where('id', '!=', $model->id);
                            if (method_exists($model, 'getAutoSortScope')) {
                                foreach ($model->getAutoSortScope() as $col => $val) {
                                    $q->where($col, $val);
                                }
                            }
                            $q->increment('sort_order');
                            return; // Handled
                        }
                    }
                }
                
                // For new records, or if oldSort was null, or if scope changed: just increment everything >= newSort in the new scope
                if ($model->exists) {
                    $query->where('id', '!=', $model->id);
                }
                $query->increment('sort_order');
            }
        });
    }
}
