@extends('layouts.admin')
@section('title', 'التقارير والإحصائيات')
@section('subtitle', 'تقارير شاملة قابلة للتصدير لإكسل')

@section('content')
<div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
    <form class="flex flex-wrap items-end gap-3" method="GET" x-data @change="$el.submit()">
        <div>
            <label class="label">من</label>
            <input type="date" name="from" class="input" value="{{ $from->toDateString() }}">
        </div>
        <div>
            <label class="label">إلى</label>
            <input type="date" name="to" class="input" value="{{ $to->toDateString() }}">
        </div>
    </form>
    <a href="{{ route('admin.reports.export', request()->only(['from','to'])) }}"
       class="btn-primary inline-flex items-center gap-2">
        تصدير Excel
    </a>
</div>

<div class="mb-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
    <div class="stat-card">
        <div class="text-xs font-bold text-slate-500">مبيعات اليوم</div>
        <div class="mt-1 text-xl font-extrabold">{{ money($todaySummary['sales']) }}</div>
        <div class="text-xs text-slate-400">{{ $todaySummary['orders'] }} فاتورة</div>
    </div>
    <div class="stat-card">
        <div class="text-xs font-bold text-slate-500">مبيعات الشهر</div>
        <div class="mt-1 text-xl font-extrabold">{{ money($monthSummary['sales']) }}</div>
        <div class="text-xs text-slate-400">{{ $monthSummary['orders'] }} فاتورة</div>
    </div>
    <div class="stat-card">
        <div class="text-xs font-bold text-slate-500">مبيعات الفترة</div>
        <div class="mt-1 text-xl font-extrabold">{{ money($summary['sales']) }}</div>
        <div class="text-xs text-slate-400">{{ $summary['orders'] }} فاتورة</div>
    </div>
    <div class="stat-card">
        <div class="text-xs font-bold text-slate-500">متوسط الفاتورة</div>
        <div class="mt-1 text-xl font-extrabold">{{ money($summary['avg']) }}</div>
        <div class="text-xs text-slate-400">خدمة {{ money($summary['service']) }}</div>
    </div>
</div>

<div class="mb-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
    <div class="stat-card">
        <div class="text-xs font-bold text-slate-500">صافي الأصناف</div>
        <div class="mt-1 text-lg font-extrabold">{{ money($summary['subtotal']) }}</div>
    </div>
    <div class="stat-card">
        <div class="text-xs font-bold text-slate-500">الخصومات</div>
        <div class="mt-1 text-lg font-extrabold text-amber-700">{{ money($summary['discounts']) }}</div>
    </div>
    <div class="stat-card">
        <div class="text-xs font-bold text-slate-500">طلبات ملغاة (الفترة)</div>
        <div class="mt-1 text-lg font-extrabold text-red-700">{{ $summary['cancelled'] }}</div>
    </div>
    <div class="stat-card">
        <div class="text-xs font-bold text-slate-500">طلبات مفتوحة/محفوظة الآن</div>
        <div class="mt-1 text-lg font-extrabold">{{ $summary['open_saved'] }}</div>
    </div>
</div>

<div class="mb-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4">
        <div class="text-xs font-bold text-emerald-700">طاولات متاحة</div>
        <div class="mt-1 text-2xl font-extrabold text-emerald-900">{{ $tableStats['available'] }} / {{ $tableStats['total'] }}</div>
    </div>
    <div class="rounded-2xl border border-red-200 bg-red-50 p-4">
        <div class="text-xs font-bold text-red-700">طاولات مشغولة</div>
        <div class="mt-1 text-2xl font-extrabold text-red-900">{{ $tableStats['occupied'] }}</div>
    </div>
    <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4">
        <div class="text-xs font-bold text-amber-700">بانتظار الدفع</div>
        <div class="mt-1 text-2xl font-extrabold text-amber-900">{{ $tableStats['awaiting'] }}</div>
    </div>
    <div class="rounded-2xl border border-slate-200 bg-white p-4">
        <div class="text-xs font-bold text-slate-500">قائمة الانتظار</div>
        <div class="mt-1 text-sm font-bold text-slate-800">
            الآن {{ $waitingStats['waiting'] }} · أُجلس {{ $waitingStats['seated'] }} · أُلغي {{ $waitingStats['cancelled'] }}
        </div>
    </div>
</div>

<div class="mb-4 grid gap-4 xl:grid-cols-2">
    <div class="card overflow-hidden">
        <div class="border-b px-5 py-4 font-extrabold text-brand-900">المبيعات اليومية</div>
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>اليوم</th><th>الطلبات</th><th>المبيعات</th><th>متوسط</th></tr></thead>
                <tbody>
                    @forelse ($daily as $row)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($row->day)->format('d/m/Y') }}</td>
                            <td>{{ $row->cnt }}</td>
                            <td class="font-bold">{{ money($row->total) }}</td>
                            <td>{{ money($row->cnt ? $row->total / $row->cnt : 0) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="py-8 text-center text-slate-400">لا بيانات</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card overflow-hidden">
        <div class="border-b px-5 py-4 font-extrabold text-brand-900">حسب الساعة (أوقات الذروة)</div>
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>الساعة</th><th>الطلبات</th><th>المبيعات</th></tr></thead>
                <tbody>
                    @forelse ($hourly as $row)
                        <tr>
                            <td>{{ str_pad((string)$row->hour, 2, '0', STR_PAD_LEFT) }}:00</td>
                            <td>{{ $row->cnt }}</td>
                            <td class="font-bold">{{ money($row->total) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="py-8 text-center text-slate-400">لا بيانات</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mb-4 grid gap-4 xl:grid-cols-2">
    <div class="card overflow-hidden">
        <div class="border-b px-5 py-4 font-extrabold text-brand-900">الأكثر مبيعاً</div>
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>#</th><th>الصنف</th><th>الكمية</th><th>الإيراد</th></tr></thead>
                <tbody>
                    @forelse ($bestSellers as $i => $row)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td class="font-bold">{{ $row->item_name }}</td>
                            <td>{{ $row->qty }}</td>
                            <td>{{ money($row->revenue) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="py-8 text-center text-slate-400">لا بيانات</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card overflow-hidden">
        <div class="border-b px-5 py-4 font-extrabold text-brand-900">أداء الكاشيرين</div>
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>الكاشير</th><th>الفواتير</th><th>المبيعات</th><th>متوسط</th></tr></thead>
                <tbody>
                    @forelse ($byCashier as $row)
                        <tr>
                            <td class="font-bold">{{ $row->cashier_name }}</td>
                            <td>{{ $row->cnt }}</td>
                            <td>{{ money($row->total) }}</td>
                            <td>{{ money($row->cnt ? $row->total / $row->cnt : 0) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="py-8 text-center text-slate-400">لا بيانات</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mb-4 grid gap-4 xl:grid-cols-2">
    <div class="card overflow-hidden">
        <div class="border-b px-5 py-4 font-extrabold text-brand-900">حسب نوع الطلب</div>
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>النوع</th><th>العدد</th><th>المبيعات</th></tr></thead>
                <tbody>
                    @forelse ($byType as $row)
                        <tr>
                            <td class="font-bold">{{ $row->order_type === 'takeaway' ? 'سفري' : 'داخلي' }}</td>
                            <td>{{ $row->cnt }}</td>
                            <td>{{ money($row->total) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="py-8 text-center text-slate-400">لا بيانات</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card overflow-hidden">
        <div class="border-b px-5 py-4 font-extrabold text-brand-900">أعلى الطاولات مبيعاً</div>
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>الطاولة</th><th>الفواتير</th><th>المبيعات</th></tr></thead>
                <tbody>
                    @forelse ($byTable as $row)
                        <tr>
                            <td class="font-bold">{{ $row->table_label }}</td>
                            <td>{{ $row->cnt }}</td>
                            <td>{{ money($row->total) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="py-8 text-center text-slate-400">لا بيانات</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mb-4 card overflow-hidden">
    <div class="border-b px-5 py-4 font-extrabold text-brand-900">الشيفتات في الفترة</div>
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>الكاشير</th>
                    <th>الحالة</th>
                    <th>الطلبات</th>
                    <th>المبيعات</th>
                    <th>العجز/الزيادة</th>
                    <th>البداية</th>
                    <th>النهاية</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($shifts as $shift)
                    <tr>
                        <td class="font-bold">{{ $shift->user?->name ?? '—' }}</td>
                        <td>{{ $shift->statusLabel() }}</td>
                        <td>{{ $shift->orders_count }}</td>
                        <td>{{ money($shift->sales_total) }}</td>
                        <td class="{{ (float)$shift->variance < 0 ? 'text-red-600 font-bold' : ((float)$shift->variance > 0 ? 'text-emerald-700 font-bold' : '') }}">
                            {{ money($shift->variance) }}
                        </td>
                        <td class="text-xs text-slate-500">{{ optional($shift->started_at)->format('H:i d/m') }}</td>
                        <td class="text-xs text-slate-500">{{ optional($shift->ended_at)->format('H:i d/m') ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="py-8 text-center text-slate-400">لا شيفتات في الفترة</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card overflow-hidden">
    <div class="border-b px-5 py-4 font-extrabold text-brand-900">آخر الفواتير في الفترة (حتى 100)</div>
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>الفاتورة</th>
                    <th>الكاشير</th>
                    <th>النوع / الطاولة</th>
                    <th>الخدمة</th>
                    <th>الخصم</th>
                    <th>الإجمالي</th>
                    <th>الوقت</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($invoicesList as $inv)
                    <tr>
                        <td class="font-bold">
                            <a class="text-brand-700 hover:underline" href="{{ route('admin.invoices.show', $inv) }}">{{ $inv->invoice_number }}</a>
                        </td>
                        <td>{{ $inv->cashier_name }}</td>
                        <td>
                            {{ $inv->order_type === 'takeaway' ? 'سفري' : 'داخلي' }}
                            @if($inv->table_label) · {{ $inv->table_label }} @endif
                        </td>
                        <td>{{ money($inv->service_amount) }}</td>
                        <td>{{ money($inv->discount_amount) }}</td>
                        <td class="font-extrabold">{{ money($inv->total) }}</td>
                        <td class="text-xs text-slate-500">{{ optional($inv->paid_at)->format('H:i d/m') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="py-8 text-center text-slate-400">لا فواتير</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection