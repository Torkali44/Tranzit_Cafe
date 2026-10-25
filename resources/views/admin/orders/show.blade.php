@extends('layouts.admin')
@section('title', 'طلب #'.$order->order_number)

@section('content')
<div class="mx-auto max-w-3xl space-y-4">
    <div class="card p-5">
        <div class="grid gap-3 sm:grid-cols-2 text-sm">
            <div><span class="text-slate-500">النوع:</span> <strong>{{ $order->typeLabel() }}</strong></div>
            <div><span class="text-slate-500">الحالة:</span> <strong>{{ $order->statusLabel() }}</strong></div>
            <div><span class="text-slate-500">الطاولة:</span> <strong>{{ $order->cafeTable?->name ?? '—' }}</strong></div>
            <div><span class="text-slate-500">الكاشير:</span> <strong>{{ $order->user?->name ?? '—' }}</strong></div>
            <div><span class="text-slate-500">الوقت:</span> <strong>{{ $order->created_at->format('H:i d/m/Y') }}</strong></div>
            @if($order->invoice)
                <div><span class="text-slate-500">الفاتورة:</span>
                    <a class="font-bold text-brand-700" href="{{ route('admin.invoices.show', $order->invoice) }}">{{ $order->invoice->invoice_number }}</a>
                </div>
            @endif
        </div>
    </div>

    <div class="card overflow-hidden">
        <table class="data-table min-w-0">
            <thead>
                <tr>
                    <th>الصنف</th>
                    <th>الكمية</th>
                    <th>السعر</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->items as $item)
                    <tr>
                        <td>
                            <div class="font-bold">{{ $item->item_name }}</div>
                            @if($item->addonsLabel())
                                <div class="text-xs text-slate-500">{{ $item->addonsLabel() }}</div>
                            @endif
                        </td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ money($item->line_total) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="space-y-1 border-t border-slate-100 px-4 py-4 text-sm">
            <div class="flex justify-between"><span>الإجمالي</span><span>{{ money($order->subtotal) }}</span></div>
            <div class="flex justify-between"><span>خدمة {{ $order->service_percent }}%</span><span>{{ money($order->service_amount) }}</span></div>
            @if($order->discount_amount > 0)
                <div class="flex justify-between text-rose-600"><span>خصم</span><span>- {{ money($order->discount_amount) }}</span></div>
            @endif
            <div class="flex justify-between text-lg font-extrabold text-brand-900"><span>المطلوب</span><span>{{ money($order->total) }}</span></div>
        </div>
    </div>

    <a href="{{ route('admin.orders.index') }}" class="btn-outline">رجوع</a>
</div>
@endsection
