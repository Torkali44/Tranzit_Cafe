@extends('layouts.pos')
@section('title', 'الطاولات')

@section('content')
<div class="mx-auto max-w-7xl p-3 sm:p-5" x-data="{ freeOpen: false, freeAction: null, freeName: '' }">
    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-lg font-extrabold text-brand-900">خريطة الطاولات</h2>
            <p class="text-sm text-slate-500">اضغط الطاولة تفتح طلبها. لما العميل يحاسب ادفع من جوه الطلب — الطاولة ترجع متاحة لوحدها.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <div class="rounded-xl bg-emerald-100 px-3 py-2 text-sm font-bold text-emerald-800">{{ $stats['available'] }} متاحة</div>
            <div class="rounded-xl bg-red-100 px-3 py-2 text-sm font-bold text-red-800">{{ $stats['occupied'] }} مشغولة</div>
            <div class="rounded-xl bg-amber-100 px-3 py-2 text-sm font-bold text-amber-800">{{ $stats['awaiting_payment'] }} بانتظار الدفع</div>
            <a href="{{ route('pos.index') }}" class="btn-primary">طلب سفري</a>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4">
        @foreach ($tables as $table)
            @php
                $order = $table->currentOrder;
                $hasRealOrder = $order && $order->items->isNotEmpty();
                $displayStatus = $table->status; // Use real status even if no order
                $border = match($displayStatus) {
                    'occupied' => 'border-red-300 bg-red-50',
                    'awaiting_payment' => 'border-amber-300 bg-amber-50',
                    default => 'border-emerald-200 bg-white',
                };
                $statusLabel = match($displayStatus) {
                    'occupied' => 'مشغولة',
                    'awaiting_payment' => 'بانتظار الدفع',
                    default => 'متاحة',
                };
            @endphp
            <div class="card border-2 p-4 {{ $border }}">
                <form method="POST" action="{{ route('pos.tables.open', $table) }}">
                    @csrf
                    <button type="submit" class="w-full text-center">
                        <div class="mb-2">
                            <span class="badge
                                {{ $displayStatus === 'available' ? 'bg-emerald-100 text-emerald-700' : '' }}
                                {{ $displayStatus === 'occupied' ? 'bg-red-100 text-red-700' : '' }}
                                {{ $displayStatus === 'awaiting_payment' ? 'bg-amber-100 text-amber-800' : '' }}
                            ">{{ $statusLabel }}</span>
                        </div>
                        <div class="text-4xl font-extrabold text-brand-900">{{ $table->number ?? $table->id }}</div>
                        <div class="mt-1 font-bold text-slate-700">{{ $table->name }}</div>
                        <div class="mt-1 text-xs text-slate-500">{{ $table->area }} · {{ $table->capacity }} مقاعد</div>
                        @if($hasRealOrder)
                            <div class="mt-3 rounded-xl bg-white/90 px-2 py-2 text-sm shadow-sm">
                                <div class="font-extrabold text-brand-900">{{ money($order->total) }}</div>
                                <div class="text-xs text-slate-500">طلب #{{ $order->order_number }}
                                    @if($displayStatus === 'awaiting_payment') — اضغط للرجوع والدفع
                                    @elseif($order->status === 'saved') — محفوظ · اضغط للرجوع
                                    @endif
                                </div>
                            </div>
                        @else
                            <div class="mt-3 text-xs font-semibold text-emerald-700">اضغط لفتح طلب جديد</div>
                        @endif
                    </button>
                </form>

                @if($hasRealOrder)
                    <button type="button"
                            class="btn-outline mt-3 w-full border-red-200 text-xs text-red-700 hover:bg-red-50"
                            @click="freeOpen=true; freeName='{{ $table->name }}'; freeAction='{{ route('pos.tables.free', $table) }}'">
                        الزبون مشي — تحرير الطاولة
                    </button>
                @elseif($displayStatus !== 'available')
                    <button type="button"
                            class="btn-outline mt-3 w-full border-red-200 text-xs text-red-700 hover:bg-red-50"
                            @click="freeOpen=true; freeName='{{ $table->name }}'; freeAction='{{ route('pos.tables.free', $table) }}'">
                        إلغاء الحجز — تحرير الطاولة
                    </button>
                @endif
            </div>
        @endforeach
    </div>

    {{-- Free table popup --}}
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" x-show="freeOpen" x-cloak>
        <div class="w-full max-w-md rounded-3xl bg-white p-5 shadow-2xl" @click.outside="freeOpen=false">
            <h3 class="text-lg font-extrabold text-brand-900">تحرير الطاولة؟</h3>
            <p class="mt-3 text-sm leading-relaxed text-slate-600">
                تحرير الطاولة <strong x-text="freeName"></strong> بعد ما الزبون يمشي؟
                <br>سيتم إلغاء أي طلب مفتوح عليها، وهترجع متاحة لفاتورة جديدة.
            </p>
            <div class="mt-5 flex gap-2">
                <form method="POST" :action="freeAction" class="flex-1">
                    @csrf
                    <input type="hidden" name="cancel_orders" value="1">
                    <button type="submit" class="btn-danger w-full">تأكيد التحرير</button>
                </form>
                <button type="button" class="btn-outline" @click="freeOpen=false">إلغاء</button>
            </div>
        </div>
    </div>
</div>
@endsection
