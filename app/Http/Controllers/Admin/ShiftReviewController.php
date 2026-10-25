<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ShiftStatus;
use App\Http\Controllers\Controller;
use App\Models\Shift;
use App\Services\ShiftService;
use Illuminate\Http\Request;

class ShiftReviewController extends Controller
{
    public function __construct(private readonly ShiftService $shifts) {}

    public function index(Request $request)
    {
        $shifts = Shift::with(['user', 'reviewer'])
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->latest('started_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.shifts.index', compact('shifts'));
    }

    public function show(Shift $shift)
    {
        $shift = $this->shifts->refreshTotals($shift);
        $shift->load(['user', 'reviewer', 'invoices.order']);

        return view('admin.shifts.show', compact('shift'));
    }

    public function review(Request $request, Shift $shift)
    {
        $data = $request->validate([
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($shift->status === ShiftStatus::Open) {
            return back()->withErrors(['shift' => 'الشيفت ما زال مفتوحاً.']);
        }

        $this->shifts->review($shift, auth()->user(), $data['notes'] ?? null);

        return back()->with('success', 'تمت مراجعة الشيفت واعتماده.');
    }
}
