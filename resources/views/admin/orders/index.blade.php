@extends('layouts.admin')
@section('title', 'الطلبات')

@section('content')
<form class="mb-4 flex flex-wrap gap-2" method="GET" x-data @change="$el.submit()" @input.debounce.700ms="$el.submit()">
    <input type="search" name="q" value="{{ request('q') }}" placeholder="رقم الطلب..." class="input max-w-xs">
    <select name="status" class="input max-w-[160px]">
        <option value="">كل الحالات</option>
        @foreach (['open'=>'مفتوح','saved'=>'محفوظ','paid'=>'مدفوع','cancelled'=>'ملغي'] as $k=>$v)
            <option value="{{ $k }}" @selected(request('status')===$k)>{{ $v }}</option>
        @endforeach
    </select>
</form>

<div class="card overflow-hidden">
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>الطلب</th>
                    <th>النوع</th>
                    <th>الطاولة</th>
                    <th>الكاشير</th>
                    <th>الإجمالي</th>
                    <th>الحالة</th>
                    <th>الوقت</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($orders as $order)
                    <tr>
                        <td><a class="font-bold text-brand-700 hover:underline" href="{{ route('admin.orders.show', $order) }}">#{{ $order->order_number }}</a></td>
                        <td>{{ $order->typeLabel() }}</td>
                        <td>{{ $order->cafeTable?->name ?? '—' }}</td>
                        <td>{{ $order->user?->name ?? '—' }}</td>
                        <td class="font-extrabold">{{ money($order->total) }}</td>
                        <td>{{ $order->statusLabel() }}</td>
                        <td class="text-slate-500">{{ $order->created_at->format('H:i d/m') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="py-10 text-center text-slate-400">لا توجد طلبات</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($orders->hasPages())
        <div class="border-t px-4 py-3">{{ $orders->links() }}</div>
    @endif
</div>
@endsection
