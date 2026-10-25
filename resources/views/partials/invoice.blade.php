@php
    $order = $invoice->order;
@endphp
<div class="invoice-sheet mx-auto max-w-lg rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
    <div class="mb-5 text-center">
        <div class="text-2xl font-extrabold text-brand-900">{{ tranzit('name_ar') }}</div>
        <div class="text-sm text-slate-500">{{ tranzit('name') }}</div>
    </div>

    <div class="mb-4 space-y-1.5 text-sm">
        <div class="flex justify-between gap-3"><span class="text-slate-500">فاتورة رقم</span><strong>{{ $invoice->invoice_number }}</strong></div>
        <div class="flex justify-between gap-3"><span class="text-slate-500">التاريخ</span><strong>{{ optional($invoice->paid_at)->format('H:i d/m/Y') }}</strong></div>
        <div class="flex justify-between gap-3"><span class="text-slate-500">الكاشير</span><strong>{{ $invoice->cashier_name }}</strong></div>
        <div class="flex justify-between gap-3"><span class="text-slate-500">{{ $invoice->order_type === 'dine_in' ? 'داخلي' : 'سفري' }}</span><strong>{{ $invoice->table_label }}</strong></div>
        <div class="flex justify-between gap-3"><span class="text-slate-500">رقم الطلب</span><strong>#{{ $order?->order_number }}</strong></div>
    </div>

    <table class="mb-4 w-full text-sm">
        <thead>
            <tr class="border-y border-slate-200 text-slate-500">
                <th class="py-2 text-start">الصنف</th>
                <th class="py-2 text-center">كمية</th>
                <th class="py-2 text-end">السعر</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order?->items ?? [] as $item)
                <tr class="border-b border-slate-100">
                    <td class="py-2">
                        <div class="font-bold">{{ $item->item_name }}</div>
                        @if($item->addonsLabel())
                            <div class="text-xs text-slate-500">{{ $item->addonsLabel() }}</div>
                        @endif
                    </td>
                    <td class="py-2 text-center">{{ $item->quantity }}</td>
                    <td class="py-2 text-end font-semibold">{{ money($item->line_total) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="space-y-1 text-sm">
        <div class="flex justify-between"><span>الإجمالي</span><span>{{ money($invoice->subtotal) }}</span></div>
        <div class="flex justify-between"><span>خدمة {{ tranzit('service_percent') }}%</span><span>{{ money($invoice->service_amount) }}</span></div>
        @if($invoice->discount_amount > 0)
            <div class="flex justify-between text-rose-600"><span>خصم</span><span>- {{ money($invoice->discount_amount) }}</span></div>
        @endif
        <div class="flex justify-between border-t border-slate-200 pt-2 text-lg font-extrabold text-brand-900">
            <span>المطلوب</span><span>{{ money($invoice->total) }}</span>
        </div>
        <div class="flex justify-between"><span>نقدي</span><span>{{ money($invoice->amount_paid) }}</span></div>
        <div class="flex justify-between"><span>الباقي</span><span>{{ money($invoice->change_amount) }}</span></div>
    </div>

    <div class="mt-6 text-center text-xs text-slate-500">
        <div>{{ tranzit('thank_you') }}</div>
        <div class="mt-1">POS System — {{ tranzit('name') }}</div>
    </div>

    @if(!empty($showActions))
        <div class="no-print mt-6 flex flex-wrap justify-center gap-2">
            <a href="{{ route('invoices.print', $invoice) }}" target="_blank" class="btn-primary">طباعة</a>
            <a href="{{ route('admin.invoices.index') }}" class="btn-outline">إغلاق</a>
        </div>
    @endif
</div>
