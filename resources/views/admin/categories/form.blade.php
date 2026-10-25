@extends('layouts.admin')
@section('title', $category->exists ? 'تعديل قسم' : 'قسم جديد')

@section('content')
<div class="mx-auto max-w-xl card p-5 sm:p-6">
    <form method="POST" action="{{ $category->exists ? route('admin.categories.update', $category) : route('admin.categories.store') }}" class="space-y-4">
        @csrf
        @if($category->exists) @method('PUT') @endif

        <div>
            <label class="label">اسم القسم</label>
            <input name="name" class="input" value="{{ old('name', $category->name) }}" required>
        </div>
        <div>
            <label class="label">الترتيب</label>
            <input type="number" name="sort_order" class="input" value="{{ old('sort_order', $category->sort_order ?? 0) }}">
        </div>
        <label class="flex items-center gap-2 text-sm font-semibold">
            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $category->is_active ?? true) ? 'checked' : '' }}>
            مفعّل
        </label>

        <div class="flex flex-wrap gap-2 pt-2">
            <button class="btn-primary" type="submit">حفظ</button>
            <a href="{{ route('admin.categories.index') }}" class="btn-outline">رجوع</a>
        </div>
    </form>
</div>
@endsection
