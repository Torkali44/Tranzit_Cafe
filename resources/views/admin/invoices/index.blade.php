@extends('layouts.admin')
@section('title', 'الفواتير')
@section('subtitle', 'فواتير '.tranzit('name'))

@section('content')
<form class="mb-4 flex flex-wrap gap-2" method="GET" x-data @change="$el.submit()" @input.debounce.700ms="$el.submit()">
    <input type="search" name="q" value="{{ request('q') }}" placeholder="رقم الفاتورة أو الكاشير..." class="input max-w-sm">
</form>

<div class="card overflow-hidden">
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>الفاتورة</th>
                    <th>الطلب</th>
                    <th>المكان</th>
                    <th>الكاشير</th>
                    <th>الإجمالي</th>
                    <th>الوقت</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($invoices as $invoice)
                    <tr>
                        <td class="font-bold">{{ $invoice->invoice_number }}</td>
                        <td>#{{ $invoice->order?->order_number }}</td>
                        <td>{{ $invoice->table_label ?? '—' }}</td>
                        <td>{{ $invoice->cashier_name }}</td>
                        <td class="font-extrabold">{{ money($invoice->total) }}</td>
                        <td class="text-slate-500">{{ optional($invoice->paid_at)->format('H:i d/m/Y') }}</td>
                        <td class="space-x-2 space-x-reverse">
                            <a href="{{ route('admin.invoices.show', $invoice) }}" class="text-sm font-bold text-brand-700">عرض</a>
                            <a href="{{ route('invoices.print', $invoice) }}" target="_blank" class="text-sm font-bold text-slate-600">طباعة</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="py-10 text-center text-slate-400">لا توجد فواتير</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($invoices->hasPages())
        <div class="border-t px-4 py-3">{{ $invoices->links() }}</div>
    @endif
</div>
@endsection
