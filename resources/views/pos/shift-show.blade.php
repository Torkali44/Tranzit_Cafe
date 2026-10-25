@extends('layouts.pos')
@section('title', 'تفاصيل الشيفت')

@section('content')
<div class="mx-auto max-w-3xl space-y-4 p-3 sm:p-5">
    <div class="card p-5">
        <div class="mb-4 flex flex-wrap justify-between gap-2">
            <h2 class="text-lg font-extrabold">شيفت #{{ $shift->id }}</h2>
            <span class="badge bg-slate-100 text-slate-700">{{ $shift->statusLabel() }}</span>
        </div>
        <div class="grid gap-3 text-sm sm:grid-cols-2">
            <div><span class="text-slate-500">من:</span> <strong>{{ $shift->started_at->format('H:i d/m/Y') }}</strong></div>
            <div><span class="text-slate-500">إلى:</span> <strong>{{ optional($shift->ended_at)->format('H:i d/m/Y') ?? '—' }}</strong></div>
            <div><span class="text-slate-500">افتتاح:</span> <strong>{{ money($shift->opening_cash) }}</strong></div>
            <div><span class="text-slate-500">مبيعات:</span> <strong>{{ money($shift->sales_total) }}</strong></div>
            <div><span class="text-slate-500">طلبات:</span> <strong>{{ $shift->orders_count }}</strong></div>
            <div><span class="text-slate-500">متوقع:</span> <strong>{{ money($shift->expected_cash) }}</strong></div>
            <div><span class="text-slate-500">فعلي:</span> <strong>{{ money($shift->closing_cash) }}</strong></div>
            <div><span class="text-slate-500">الفرق:</span>
                <strong class="{{ ($shift->variance ?? 0) < 0 ? 'text-red-600' : 'text-emerald-700' }}">{{ money($shift->variance) }}</strong>
            </div>
        </div>
        @if($shift->notes)
            <p class="mt-4 rounded-xl bg-slate-50 p-3 text-sm">{{ $shift->notes }}</p>
        @endif
    </div>

    <div class="card overflow-hidden">
        <div class="border-b px-5 py-4 font-extrabold">فواتير الشيفت</div>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>الفاتورة</th>
                        <th>الإجمالي</th>
                        <th>الوقت</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($shift->invoices as $inv)
                        <tr>
                            <td>{{ $inv->invoice_number }}</td>
                            <td>{{ money($inv->total) }}</td>
                            <td>{{ optional($inv->paid_at)->format('H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <a href="{{ route('pos.shift.index') }}" class="btn-outline">رجوع</a>
</div>
@endsection
