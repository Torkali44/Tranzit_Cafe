@extends('layouts.admin')
@section('title', 'الأقسام')
@section('subtitle', 'إدارة أقسام القائمة')

@section('content')
<div class="mb-4 flex flex-wrap items-center justify-between gap-3">
    <p class="text-sm text-slate-500">{{ $categories->count() }} قسم</p>
    <a href="{{ route('admin.categories.create') }}" class="btn-primary">+ قسم جديد</a>
</div>

<div class="card overflow-hidden">
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>الاسم</th>
                    <th>المنتجات</th>
                    <th>الترتيب</th>
                    <th>الحالة</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($categories as $category)
                    <tr>
                        <td>{{ $category->id }}</td>
                        <td class="font-bold">{{ $category->name }}</td>
                        <td>{{ $category->items_count }}</td>
                        <td>{{ $category->sort_order }}</td>
                        <td>
                            <span class="badge {{ $category->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                {{ $category->is_active ? 'مفعّل' : 'موقوف' }}
                            </span>
                        </td>
                        <td class="space-x-2 space-x-reverse">
                            <a href="{{ route('admin.categories.edit', $category) }}" class="text-sm font-bold text-brand-700">تعديل</a>
                            <form class="inline" method="POST" action="{{ route('admin.categories.destroy', $category) }}" onsubmit="return confirm('حذف القسم؟')">
                                @csrf @method('DELETE')
                                <button class="text-sm font-bold text-red-600">حذف</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
