@extends('layouts.admin')
@section('title', 'مراجعة الشيفتات')
@section('subtitle', 'مقارنة مبيعات الكاشير مع فلوس الخزنة')

@section('content')
<form class="mb-4 flex flex-wrap gap-2" method="GET" x-data @change="$el.submit()" @input.debounce.700ms="$el.submit()">
    <select name="status" class="input max-w-[200px]">
        <option value="">كل الحالات</option>
        <option value="open" @selected(request('status')==='open')>مفتوح</option>
        <option value="closed" @selected(request('status')==='closed')>بانتظار المراجعة</option>
        <option value="reviewed" @selected(request('status')==='reviewed')>تمت المراجعة</option>
    </select>
</form>

<div class="card overflow-hidden">
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>الكاشير</th>
                    <th>البداية</th>
                    <th>الطلبات</th>
                    <th>المبيعات</th>
                    <th>المتوقع</th>
                    <th>الفعلي</th>
                    <th>الفرق</th>
                    <th>الحالة</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($shifts as $shift)
                    <tr>
                        <td class="font-bold">{{ $shift->user?->name }}</td>
                        <td>{{ $shift->started_at->format('H:i d/m') }}</td>
                        <td>{{ $shift->orders_count }}</td>
                        <td>{{ money($shift->sales_total) }}</td>
                        <td>{{ money($shift->expected_cash) }}</td>
                        <td>{{ $shift->closing_cash !== null ? money($shift->closing_cash) : '—' }}</td>
                        <td class="{{ ($shift->variance ?? 0) < 0 ? 'font-bold text-red-600' : '' }}">
                            {{ $shift->variance !== null ? money($shift->variance) : '—' }}
                        </td>
                        <td>{{ $shift->statusLabel() }}</td>
                        <td><a href="{{ route('admin.shifts.show', $shift) }}" class="font-bold text-brand-700">عرض</a></td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="py-10 text-center text-slate-400">لا شيفتات بعد</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($shifts->hasPages())
        <div class="border-t px-4 py-3">{{ $shifts->links() }}</div>
    @endif
</div>
@endsection
