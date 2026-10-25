@extends('layouts.pos')
@section('title', 'الشيفت والخزنة')

@section('content')
<div class="mx-auto max-w-3xl space-y-4 p-3 sm:p-5">
    @if($current)
        <div class="card p-5">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
                <h2 class="text-lg font-extrabold text-brand-900">الشيفت الحالي</h2>
                <span class="badge bg-emerald-100 text-emerald-700">مفتوح</span>
            </div>
            <div class="grid gap-3 sm:grid-cols-2 text-sm">
                <div class="rounded-xl bg-slate-50 p-3">
                    <div class="text-xs text-slate-500">بداية الشيفت</div>
                    <div class="font-bold">{{ $current->started_at->format('H:i d/m/Y') }}</div>
                </div>
                <div class="rounded-xl bg-slate-50 p-3">
                    <div class="text-xs text-slate-500">افتتاح الخزنة</div>
                    <div class="font-bold">{{ money($current->opening_cash) }}</div>
                </div>
                <div class="rounded-xl bg-slate-50 p-3">
                    <div class="text-xs text-slate-500">عدد الأوردرات المدفوعة</div>
                    <div class="text-xl font-extrabold">{{ $current->orders_count }}</div>
                </div>
                <div class="rounded-xl bg-slate-50 p-3">
                    <div class="text-xs text-slate-500">إجمالي المبيعات</div>
                    <div class="text-xl font-extrabold text-brand-800">{{ money($current->sales_total) }}</div>
                </div>
                <div class="rounded-xl bg-emerald-50 p-3 sm:col-span-2">
                    <div class="text-xs text-emerald-700">المتوقع في الخزنة الآن</div>
                    <div class="text-2xl font-extrabold text-emerald-800">{{ money($current->expected_cash) }}</div>
                    <div class="text-xs text-slate-500">افتتاح + المبيعات</div>
                </div>
            </div>

            <form method="POST" action="{{ route('pos.shift.close') }}" class="mt-5 space-y-3 border-t border-slate-100 pt-5">
                @csrf
                <h3 class="font-extrabold text-brand-900">إغلاق الشيفت ومراجعة الحساب</h3>
                <p class="text-xs text-slate-500">عدّ الفلوس في الخزنة وسجّل المبلغ. النظام يحسب العجز/الزيادة تلقائياً لمراجعته مع الإدارة.</p>
                <div>
                    <label class="label">المبلغ الفعلي في الخزنة</label>
                    <input type="number" step="0.01" min="0" name="closing_cash" class="input" required
                           value="{{ old('closing_cash', $current->expected_cash) }}">
                </div>
                <div>
                    <label class="label">ملاحظات (اختياري)</label>
                    <textarea name="notes" class="input" rows="2">{{ old('notes') }}</textarea>
                </div>
                <button type="submit" class="btn-danger w-full" onclick="return confirm('تأكيد إغلاق الشيفت؟')">إغلاق الشيفت ومراجعة الحساب</button>
            </form>
        </div>
    @else
        <div class="card p-5">
            <h2 class="mb-2 text-lg font-extrabold text-brand-900">فتح شيفت جديد</h2>
            <p class="mb-4 text-sm text-slate-500">قبل البيع لازم تفتح شيفت. سجّل مبلغ الافتتاح في الخزنة إن وُجد.</p>
            <form method="POST" action="{{ route('pos.shift.start') }}" class="space-y-3">
                @csrf
                <div>
                    <label class="label">مبلغ افتتاح الخزنة</label>
                    <input type="number" step="0.01" min="0" name="opening_cash" class="input" value="{{ old('opening_cash', 0) }}" required>
                </div>
                <button type="submit" class="btn-success w-full py-3">بدء الشيفت</button>
            </form>
        </div>
    @endif

    <div class="card overflow-hidden">
        <div class="border-b px-5 py-4 font-extrabold text-brand-900">آخر الشيفتات</div>
        <div class="divide-y">
            @forelse($history as $shift)
                <a href="{{ route('pos.shift.show', $shift) }}" class="flex items-center justify-between gap-3 px-5 py-3 hover:bg-slate-50">
                    <div>
                        <div class="font-bold">{{ $shift->started_at->format('d/m/Y H:i') }}</div>
                        <div class="text-xs text-slate-500">{{ $shift->statusLabel() }} · {{ $shift->orders_count }} طلب</div>
                    </div>
                    <div class="text-end">
                        <div class="font-extrabold">{{ money($shift->sales_total) }}</div>
                        @if($shift->variance !== null)
                            <div class="text-xs {{ $shift->variance < 0 ? 'text-red-600' : ($shift->variance > 0 ? 'text-amber-600' : 'text-emerald-600') }}">
                                فرق: {{ money($shift->variance) }}
                            </div>
                        @endif
                    </div>
                </a>
            @empty
                <div class="p-8 text-center text-slate-400">لا يوجد سجل بعد</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
