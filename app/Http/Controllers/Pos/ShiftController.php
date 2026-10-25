<?php

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use App\Models\Shift;
use App\Services\ShiftService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ShiftController extends Controller
{
    public function __construct(private readonly ShiftService $shifts) {}

    public function index()
    {
        $current = $this->shifts->currentOpenShift();

        if ($current) {
            $current = $this->shifts->refreshTotals($current);
        }

        $history = Shift::query()
            ->where('user_id', auth()->id())
            ->where('status', '!=', 'open')
            ->latest('ended_at')
            ->limit(10)
            ->get();

        return view('pos.shift', compact('current', 'history'));
    }

    public function start(Request $request)
    {
        $data = $request->validate([
            'opening_cash' => ['nullable', 'numeric', 'min:0'],
        ]);

        try {
            $this->shifts->start(auth()->user(), (float) ($data['opening_cash'] ?? 0));
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return redirect()->route('pos.index')->with('success', 'تم فتح الشيفت. بالتوفيق!');
    }

    public function close(Request $request)
    {
        $data = $request->validate([
            'closing_cash' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $shift = $this->shifts->currentOpenShift();

        if (! $shift) {
            return back()->withErrors(['shift' => 'لا يوجد شيفت مفتوح.']);
        }

        try {
            $closed = $this->shifts->close($shift, (float) $data['closing_cash'], $data['notes'] ?? null);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return redirect()->route('pos.shift.index')->with([
            'success' => 'تم إغلاق الشيفت. راجع النتيجة مع الإدارة.',
            'closed_shift_id' => $closed->id,
        ]);
    }

    public function show(Shift $shift)
    {
        abort_unless($shift->user_id === auth()->id() || auth()->user()->isAdmin(), 403);

        $shift = $this->shifts->refreshTotals($shift);
        $shift->load(['user', 'reviewer', 'invoices']);

        return view('pos.shift-show', compact('shift'));
    }
}
