@extends('layouts.admin')
@section('title', 'قائمة الانتظار')
@section('subtitle', 'عملاء بانتظار ترابيزة')

@section('content')
@php
    $popupError = session('popup_error');
@endphp
<div x-data="{
        popupOpen: {{ $popupError ? 'true' : 'false' }},
        popupMsg: @js($popupError ?: ''),
        showPopup(msg) { this.popupMsg = msg; this.popupOpen = true; },
        checkSeat(form) {
            const select = form.querySelector('[name=cafe_table_id]');
            const option = select?.selectedOptions?.[0];
            if (!option || !option.value) { this.showPopup('اختَر ترابيزة أولاً.'); return false; }
            const capacity = Number(option.dataset.capacity || 0);
            const party = Number(form.dataset.party || 0);
            if (capacity > 0 && party > capacity) {
                this.showPopup('عدد الأفراد (' + party + ') أكبر من سعة الترابيزة (' + capacity + ').');
                return false;
            }
            return true;
        }
     }">
<div class="grid gap-4 lg:grid-cols-3">
    <div class="card p-5 lg:col-span-1">
        <h2 class="mb-4 font-extrabold text-brand-900">إضافة للانتظار</h2>
        <form method="POST" action="{{ route('admin.waiting.store') }}" class="space-y-3">
            @csrf
            <div>
                <label class="label">اسم العميل</label>
                <input name="customer_name" class="input" required>
            </div>
            <div>
                <label class="label">عدد الأفراد</label>
                <input type="number" name="party_size" class="input" value="2" min="1" required>
            </div>
            <div>
                <label class="label">الهاتف</label>
                <input name="phone" class="input">
            </div>
            <div>
                <label class="label">ملاحظات</label>
                <textarea name="notes" class="input" rows="2"></textarea>
            </div>
            <button class="btn-primary w-full" type="submit">إضافة</button>
        </form>
    </div>

    <div class="space-y-4 lg:col-span-2">
        <div class="card overflow-hidden">
            <div class="border-b border-slate-100 px-5 py-4 font-extrabold text-brand-900">
                حالياً في الانتظار ({{ $waiting->count() }})
            </div>
            <div class="divide-y divide-slate-100">
                @forelse ($waiting as $row)
                    <div class="flex flex-col gap-3 p-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <div class="font-extrabold">{{ $row->customer_name }}</div>
                            <div class="text-xs text-slate-500">
                                {{ $row->party_size }} أفراد
                                @if($row->phone) · {{ $row->phone }} @endif
                                · منذ {{ $row->created_at->diffForHumans() }}
                            </div>
                            @if($row->notes)<div class="mt-1 text-xs text-slate-400">{{ $row->notes }}</div>@endif
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <form method="POST" action="{{ route('admin.waiting.seat', $row) }}" class="flex flex-wrap gap-2"
                                  data-party="{{ $row->party_size }}"
                                  @submit="if (!checkSeat($el)) $event.preventDefault()">
                                @csrf
                                <select name="cafe_table_id" class="input w-auto min-w-[140px]" required>
                                    <option value="">اختر ترابيزة</option>
                                    @foreach($availableTables as $t)
                                        <option value="{{ $t->id }}" data-capacity="{{ $t->capacity }}">{{ $t->name }} ({{ $t->capacity }})</option>
                                    @endforeach
                                </select>
                                <button class="btn-success text-xs" type="submit">إجلاس</button>
                            </form>
                            <form method="POST" action="{{ route('admin.waiting.cancel', $row) }}">
                                @csrf
                                <button class="btn-ghost text-xs text-red-600">إلغاء</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-slate-400">القائمة فارغة</div>
                @endforelse
            </div>
        </div>

        <div class="card overflow-hidden">
            <div class="border-b border-slate-100 px-5 py-4 font-extrabold text-brand-900">سجل حديث</div>
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>العميل</th>
                            <th>العدد</th>
                            <th>الحالة</th>
                            <th>الطاولة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($history as $row)
                            <tr>
                                <td class="font-bold">{{ $row->customer_name }}</td>
                                <td>{{ $row->party_size }}</td>
                                <td>{{ $row->statusLabel() }}</td>
                                <td>{{ $row->cafeTable?->name ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
     x-show="popupOpen" x-cloak @keydown.escape.window="popupOpen=false">
    <div class="w-full max-w-md rounded-3xl bg-white p-5 shadow-2xl" @click.outside="popupOpen=false">
        <h3 class="text-lg font-extrabold text-brand-900">تعذر الإجلاس</h3>
        <p class="mt-2 text-sm leading-relaxed text-slate-600" x-text="popupMsg"></p>
        <button type="button" class="btn-primary mt-5 w-full" @click="popupOpen=false">حسناً</button>
    </div>
</div>
</div>
@endsection