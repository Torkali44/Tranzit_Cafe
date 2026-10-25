<?php

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use App\Models\CafeTable;
use App\Models\WaitingList;
use Illuminate\Http\Request;

class WaitingListController extends Controller
{
    public function index()
    {
        $waiting = WaitingList::with('cafeTable')
            ->where('status', 'waiting')
            ->latest()
            ->get();

        $history = WaitingList::with('cafeTable')
            ->where('status', '!=', 'waiting')
            ->latest()
            ->limit(20)
            ->get();

        $availableTables = CafeTable::where('is_active', true)
            ->where('status', 'available')
            ->orderBy('sort_order')
            ->get();

        return view('pos.waiting.index', compact('waiting', 'history', 'availableTables'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:120'],
            'party_size' => ['required', 'integer', 'min:1', 'max:50'],
            'phone' => ['nullable', 'string', 'max:30'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        WaitingList::create($data + ['status' => 'waiting']);

        return back()->with('success', 'تمت إضافة العميل لقائمة الانتظار.');
    }

    public function seat(Request $request, WaitingList $waiting)
    {
        $data = $request->validate([
            'cafe_table_id' => ['required', 'exists:cafe_tables,id'],
        ]);

        $table = CafeTable::findOrFail($data['cafe_table_id']);

        if ($table->status !== 'available') {
            return back()->with('popup_error', 'الترابيزة غير متاحة.');
        }

        if ($table->capacity < $waiting->party_size) {
            return back()->with(
                'popup_error',
                'عدد الأفراد ('.$waiting->party_size.') أكبر من سعة الترابيزة ('.$table->capacity.').'
            );
        }

        $waiting->update([
            'status' => 'seated',
            'cafe_table_id' => $table->id,
            'seated_at' => now(),
        ]);

        $table->update(['status' => 'occupied']);

        return back()->with('success', 'تم إجلاس العميل على '.$table->name);
    }

    public function cancel(WaitingList $waiting)
    {
        $waiting->update(['status' => 'cancelled']);

        return back()->with('success', 'تم إلغاء الانتظار.');
    }
}
