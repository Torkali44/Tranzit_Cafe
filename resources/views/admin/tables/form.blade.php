@extends('layouts.admin')
@section('title', $table->exists ? 'تعديل ترابيزة' : 'ترابيزة جديدة')

@section('content')
<div class="mx-auto max-w-xl card p-5 sm:p-6">
    <form method="POST" action="{{ $table->exists ? route('admin.tables.update', $table) : route('admin.tables.store') }}" class="space-y-4">
        @csrf
        @if($table->exists) @method('PUT') @endif

        <div>
            <label class="label">الاسم</label>
            <input name="name" class="input" value="{{ old('name', $table->name) }}" required placeholder="ترابيزة 1 / شرفة 10">
        </div>
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="label">الرقم</label>
                <input name="number" class="input" value="{{ old('number', $table->number) }}">
            </div>
            <div>
                <label class="label">السعة</label>
                <input type="number" name="capacity" class="input" value="{{ old('capacity', $table->capacity ?? 4) }}" min="1" required>
            </div>
            <div>
                <label class="label">المنطقة</label>
                <select name="area" class="input">
                    @foreach (['داخلي','شرفة','خارجي'] as $area)
                        <option value="{{ $area }}" @selected(old('area', $table->area) === $area)>{{ $area }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="label">الحالة</label>
                <select name="status" class="input">
                    <option value="available" @selected(old('status', $table->status) === 'available')>متاحة</option>
                    <option value="occupied" @selected(old('status', $table->status) === 'occupied')>مشغولة</option>
                    <option value="awaiting_payment" @selected(old('status', $table->status) === 'awaiting_payment')>بانتظار الدفع</option>
                </select>
            </div>
        </div>
        <div>
            <label class="label">الترتيب</label>
            <input type="number" name="sort_order" class="input" value="{{ old('sort_order', $table->sort_order ?? 0) }}">
        </div>
        <label class="flex items-center gap-2 text-sm font-semibold">
            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $table->is_active ?? true) ? 'checked' : '' }}>
            مفعّلة
        </label>

        <div class="flex flex-wrap gap-2 pt-2">
            <button class="btn-primary" type="submit">حفظ</button>
            <a href="{{ route('admin.tables.index') }}" class="btn-outline">رجوع</a>
        </div>
    </form>
</div>
@endsection
