@extends('layouts.admin')
@section('title', 'شيفت #'.$shift->id)

@section('content')
<div class="mx-auto max-w-3xl space-y-4">
    <div class="card p-5">
        <div class="mb-3 flex flex-wrap justify-between gap-2">
            <div>
                <div class="text-lg font-extrabold">{{ $shift->user?->name }}</div>
                <div class="text-sm text-slate-500">{{ $shift->statusLabel() }}</div>
            </div>
            @if(($shift->variance ?? 0) < 0)
                <span class="badge bg-red-100 text-red-700">عجز {{ money(abs($shift->variance)) }}</span>
            @elseif(($shift->variance ?? 0) > 0)
                <span class="badge bg-amber-100 text-amber-800">زيادة {{ money($shift->variance) }}</span>
            @elseif($shift->variance !== null)
                <span class="badge bg-emerald-100 text-emerald-700">مطابق</span>
            @endif
        </div>
        <div class="grid gap-3 text-sm sm:grid-cols-2">
            <div>من: <strong>{{ $shift->started_at->format('H:i d/m/Y') }}</strong></div>
            <div>إلى: <strong>{{ optional($shift->ended_at)->format('H:i d/m/Y') ?? '—' }}</strong></div>
            <div>افتتاح: <strong>{{ money($shift->opening_cash) }}</strong></div>
            <div>مبيعات: <strong>{{ money($shift->sales_total) }}</strong></div>
            <div>خصومات: <strong>{{ money($shift->discounts_total) }}</strong></div>
            <div>طلبات: <strong>{{ $shift->orders_count }}</strong></div>
            <div>متوقع بالخزنة: <strong>{{ money($shift->expected_cash) }}</strong></div>
            <div>فعلي بالخزنة: <strong>{{ money($shift->closing_cash) }}</strong></div>
        </div>
        @if($shift->notes)
            <p class="mt-4 rounded-xl bg-slate-50 p-3 text-sm">{{ $shift->notes }}</p>
        @endif

        @if($shift->status->value === 'closed')
            <form method="POST" action="{{ route('admin.shifts.review', $shift) }}" class="mt-5 space-y-3 border-t pt-4">
                @csrf
                <label class="label">ملاحظات المراجعة</label>
                <textarea name="notes" class="input" rows="2">{{ $shift->notes }}</textarea>
                <button class="btn-primary" type="submit">اعتماد المراجعة</button>
            </form>
        @elseif($shift->reviewed_at)
            <p class="mt-4 text-sm text-slate-500">راجعها {{ $shift->reviewer?->name }} في {{ $shift->reviewed_at->format('H:i d/m') }}</p>
        @endif
    </div>

    <div class="card overflow-hidden">
        <div class="border-b px-5 py-4 font-extrabold">فواتير الشيفت</div>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr><th>الفاتورة</th><th>المكان</th><th>الإجمالي</th><th>الوقت</th></tr>
                </thead>
                <tbody>
                    @foreach($shift->invoices as $inv)
                        <tr>
                            <td><a class="font-bold text-brand-700" href="{{ route('admin.invoices.show', $inv) }}">{{ $inv->invoice_number }}</a></td>
                            <td>{{ $inv->table_label }}</td>
                            <td>{{ money($inv->total) }}</td>
                            <td>{{ optional($inv->paid_at)->format('H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <a href="{{ route('admin.shifts.index') }}" class="btn-outline">رجوع</a>
</div>
@endsection
