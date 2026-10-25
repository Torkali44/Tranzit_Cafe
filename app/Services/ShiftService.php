<?php

namespace App\Services;

use App\Enums\ShiftStatus;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ShiftService
{
    public function currentOpenShift(?User $user = null): ?Shift
    {
        $user ??= auth()->user();

        if (! $user) {
            return null;
        }

        return Shift::query()
            ->where('user_id', $user->id)
            ->where('status', ShiftStatus::Open)
            ->latest('started_at')
            ->first();
    }

    public function start(User $user, float $openingCash = 0): Shift
    {
        if ($this->currentOpenShift($user)) {
            throw ValidationException::withMessages([
                'shift' => 'لديك شيفت مفتوح بالفعل.',
            ]);
        }

        return Shift::create([
            'user_id' => $user->id,
            'status' => ShiftStatus::Open,
            'opening_cash' => $openingCash,
            'started_at' => now(),
        ]);
    }

    public function refreshTotals(Shift $shift): Shift
    {
        $invoices = $shift->invoices();

        $shift->update([
            'orders_count' => (clone $invoices)->count(),
            'sales_total' => (clone $invoices)->sum('total'),
            'discounts_total' => (clone $invoices)->sum('discount_amount'),
            'expected_cash' => (float) $shift->opening_cash + (float) (clone $invoices)->sum('total'),
        ]);

        return $shift->fresh();
    }

    public function close(Shift $shift, float $closingCash, ?string $notes = null): Shift
    {
        if (! $shift->isOpen()) {
            throw ValidationException::withMessages([
                'shift' => 'هذا الشيفت مغلق مسبقاً.',
            ]);
        }

        return DB::transaction(function () use ($shift, $closingCash, $notes) {
            $shift = $this->refreshTotals($shift);
            $variance = round($closingCash - (float) $shift->expected_cash, 2);

            $shift->update([
                'status' => ShiftStatus::Closed,
                'closing_cash' => $closingCash,
                'variance' => $variance,
                'notes' => $notes,
                'ended_at' => now(),
            ]);

            return $shift->fresh(['user', 'invoices']);
        });
    }

    public function review(Shift $shift, User $admin, ?string $notes = null): Shift
    {
        if ($shift->status === ShiftStatus::Open) {
            throw ValidationException::withMessages([
                'shift' => 'يجب إغلاق الشيفت أولاً قبل المراجعة.',
            ]);
        }

        $shift->update([
            'status' => ShiftStatus::Reviewed,
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
            'notes' => $notes ?? $shift->notes,
        ]);

        return $shift->fresh(['user', 'reviewer']);
    }
}
