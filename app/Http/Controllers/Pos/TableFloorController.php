<?php

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use App\Models\CafeTable;
use App\Services\OrderService;
use App\Services\ShiftService;
use Illuminate\Http\Request;

class TableFloorController extends Controller
{
    public function __construct(
        private readonly OrderService $orders,
        private readonly ShiftService $shifts,
    ) {}

    public function index()
    {
        $this->orders->healStaleTables();

        $tables = CafeTable::where('is_active', true)
            ->with(['currentOrder.items'])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        // Occupied / awaiting count whether they have an order or not
        $occupied = $tables->filter(fn ($t) => $t->status === 'occupied')->count();
        $awaiting = $tables->filter(fn ($t) => $t->status === 'awaiting_payment')->count();
        $available = $tables->count() - $occupied - $awaiting;

        $stats = [
            'available' => $available,
            'occupied' => $occupied,
            'awaiting_payment' => $awaiting,
        ];

        return view('pos.tables', compact('tables', 'stats'));
    }

    public function open(CafeTable $table)
    {
        if (auth()->user()->isCashier() && ! $this->shifts->currentOpenShift()) {
            return redirect()->route('pos.shift.index')
                ->with('warning', 'افتح الشيفت أولاً.');
        }

        $this->orders->healStaleTables();
        $table->refresh();
        $table->load(['currentOrder.items']);

        $order = $table->currentOrder;

        // Resume real order with items
        if ($order && $order->items->isNotEmpty()) {
            return redirect()->route('pos.index', ['order' => $order->id]);
        }

        // Empty leftover order — cancel and treat as available
        if ($order) {
            $this->orders->cancel($order);
            $table->refresh();
        }

        if ($table->status !== 'available') {
            $this->orders->freeTable($table, true);
        }

        // Don't create empty order — open POS bound to this table
        return redirect()->route('pos.index', ['table' => $table->id]);
    }

    public function free(Request $request, CafeTable $table)
    {
        $request->validate([
            'cancel_orders' => ['nullable', 'boolean'],
        ]);

        if ($table->status === 'available' && ! $table->currentOrder) {
            return back()->with('success', 'الطاولة متاحة بالفعل.');
        }

        if ($table->status !== 'available' && ! $table->currentOrder) {
            // It's occupied but has no order (e.g. just seated from waiting list)
            $table->update(['status' => 'available']);
            return back()->with('success', 'تم إلغاء الحجز وتحرير الطاولة.');
        }

        $this->orders->freeTable($table, $request->boolean('cancel_orders', true));

        return back()->with('success', 'تم تحرير الطاولة '.$table->name.' وأصبحت متاحة.');
    }
}
