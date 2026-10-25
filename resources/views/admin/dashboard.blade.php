@extends('layouts.admin')

@section('title', 'لوحة التحكم')
@section('subtitle', 'ملخص اليوم والشهر')

@section('content')
<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
    <div class="stat-card">
        <div>
            <div class="text-xs font-bold text-slate-500">مبيعات اليوم</div>
            <div class="mt-1 text-xl font-extrabold text-brand-900 sm:text-2xl">{{ money($todaySales) }}</div>
        </div>
        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-700">💰</div>
    </div>
    <div class="stat-card">
        <div>
            <div class="text-xs font-bold text-slate-500">طلبات اليوم</div>
            <div class="mt-1 text-xl font-extrabold text-brand-900 sm:text-2xl">{{ $todayOrders }}</div>
        </div>
        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-sky-100 text-sky-700">🧺</div>
    </div>
    <div class="stat-card">
        <div>
            <div class="text-xs font-bold text-slate-500">متوسط الفاتورة</div>
            <div class="mt-1 text-xl font-extrabold text-brand-900 sm:text-2xl">{{ money($avgInvoice) }}</div>
        </div>
        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-100 text-indigo-700">📄</div>
    </div>
    <div class="stat-card">
        <div>
            <div class="text-xs font-bold text-slate-500">خصومات اليوم</div>
            <div class="mt-1 text-xl font-extrabold text-brand-900 sm:text-2xl">{{ money($todayDiscounts) }}</div>
        </div>
        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-rose-100 text-rose-700">٪</div>
    </div>
</div>

<div class="mt-4 grid gap-4 lg:grid-cols-3">
    <div class="card p-5 lg:col-span-1">
        <div class="mb-4 flex items-center justify-between">
            <h2 class="font-extrabold text-brand-900">حالة الطاولات</h2>
            <a href="{{ route('pos.tables') }}" class="text-xs font-bold text-brand-600 hover:underline">فتح خريطة الطاولات</a>
        </div>
        <div class="space-y-3">
            @php
                $rows = [
                    ['متاحة', $tableStats['available'], 'bg-emerald-500', $tableStats['total']],
                    ['مشغولة', $tableStats['occupied'], 'bg-red-500', $tableStats['total']],
                    ['بانتظار الدفع', $tableStats['awaiting_payment'], 'bg-amber-500', $tableStats['total']],
                ];
            @endphp
            @foreach ($rows as [$label, $count, $bar, $total])
                <div>
                    <div class="mb-1 flex justify-between text-sm">
                        <span class="font-semibold">{{ $label }}</span>
                        <span class="text-slate-500">{{ $count }}/{{ max($total,1) }}</span>
                    </div>
                    <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                        <div class="h-full {{ $bar }}" style="width: {{ $total ? ($count/$total)*100 : 0 }}%"></div>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="mt-5 grid grid-cols-2 gap-2 rounded-xl bg-slate-50 p-3 text-center text-sm">
            <div>
                <div class="text-xs text-slate-500">مبيعات الشهر</div>
                <div class="font-extrabold text-brand-900">{{ money($monthSales) }}</div>
            </div>
            <div>
                <div class="text-xs text-slate-500">طلبات الشهر</div>
                <div class="font-extrabold text-brand-900">{{ $monthOrders }}</div>
            </div>
        </div>
    </div>

    <div class="card p-5 lg:col-span-2">
        <h2 class="mb-4 font-extrabold text-brand-900">مبيعات آخر 14 يوماً</h2>
        <div class="h-56">
            <canvas id="salesChart"></canvas>
        </div>
    </div>
</div>

<div class="mt-4 grid gap-4 lg:grid-cols-2">
    <div class="card overflow-hidden">
        <div class="border-b border-slate-100 px-5 py-4 font-extrabold text-brand-900">أحدث الفواتير</div>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>الفاتورة</th>
                        <th>النوع</th>
                        <th>الإجمالي</th>
                        <th>الوقت</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($latestInvoices as $inv)
                        <tr>
                            <td>
                                <a class="font-bold text-brand-700 hover:underline" href="{{ route('admin.invoices.show', $inv) }}">{{ $inv->invoice_number }}</a>
                            </td>
                            <td>{{ $inv->order_type === 'dine_in' ? 'داخلي' : 'سفري' }}</td>
                            <td class="font-bold">{{ money($inv->total) }}</td>
                            <td class="text-slate-500">{{ optional($inv->paid_at)->format('H:i d/m') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="py-8 text-center text-slate-400">لا توجد فواتير بعد</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card p-5">
        <h2 class="mb-4 font-extrabold text-brand-900">الأكثر مبيعاً (هذا الشهر)</h2>
        <div class="space-y-3">
            @forelse ($bestSellers as $i => $row)
                <div class="flex items-center gap-3 rounded-xl bg-slate-50 px-3 py-3">
                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-brand-900 text-sm font-bold text-white">{{ $i+1 }}</div>
                    <div class="min-w-0 flex-1">
                        <div class="truncate font-bold">{{ $row->item_name }}</div>
                        <div class="text-xs text-slate-500">{{ $row->qty }} قطعة</div>
                    </div>
                    <div class="font-extrabold text-brand-800">{{ money($row->revenue) }}</div>
                </div>
            @empty
                <div class="py-8 text-center text-slate-400">لا بيانات بعد</div>
            @endforelse
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
new Chart(document.getElementById('salesChart'), {
    type: 'bar',
    data: {
        labels: @json($chartLabels),
        datasets: [{
            label: 'المبيعات',
            data: @json($chartValues),
            backgroundColor: '#1f4e79',
            borderRadius: 8,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, ticks: { font: { family: 'Cairo' } } },
            x: { ticks: { font: { family: 'Cairo' } } }
        }
    }
});
</script>
@endpush
