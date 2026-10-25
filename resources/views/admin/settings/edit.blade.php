@extends('layouts.admin')
@section('title', 'الإعدادات')
@section('subtitle', 'نسبة الخدمة واسم المحل')

@section('content')
<div class="mx-auto max-w-xl card p-5 sm:p-6">
    <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-4">
        @csrf
        @method('PUT')

        <div>
            <label class="label">اسم الكافيه (EN)</label>
            <input name="cafe_name" class="input" value="{{ old('cafe_name', $settings['cafe_name']) }}" required>
        </div>
        <div>
            <label class="label">اسم الكافيه (عربي)</label>
            <input name="cafe_name_ar" class="input" value="{{ old('cafe_name_ar', $settings['cafe_name_ar']) }}" required>
        </div>
        <div>
            <label class="label">نسبة الخدمة %</label>
            <input type="number" step="0.01" min="0" max="100" name="service_percent" class="input"
                   value="{{ old('service_percent', $settings['service_percent']) }}" required>
            <p class="mt-1 text-xs text-slate-500">تُطبَّق على الطلبات الجديدة بعد الحفظ.</p>
        </div>

        <button type="submit" class="btn-primary">حفظ الإعدادات</button>
    </form>
</div>
@endsection
