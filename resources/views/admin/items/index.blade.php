@extends('layouts.admin')
@section('title', 'المنتجات')
@section('subtitle', 'الاسم والسعر والإضافات')

@section('content')
<div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <form class="flex flex-1 flex-wrap gap-2" method="GET" x-data @change="$el.submit()" @input.debounce.700ms="$el.submit()">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="بحث عن منتج..." class="input max-w-xs">
        <select name="category_id" class="input max-w-[200px]">
            <option value="">كل الأقسام</option>
            @foreach($categories as $cat)
                <option value="{{ $cat->id }}" @selected(request('category_id') == $cat->id)>{{ $cat->name }}</option>
            @endforeach
    </select>
</form>
    <a href="{{ route('admin.items.create') }}" class="btn-primary">+ منتج جديد</a>
</div>

<div class="card overflow-hidden">
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>المنتج</th>
                    <th>القسم</th>
                    <th>السعر</th>
                    <th>الحالة</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($items as $item)
                    <tr>
                        <td class="font-bold">{{ $item->name }}</td>
                        <td>{{ $item->category?->name }}</td>
                        <td class="font-extrabold text-brand-800">{{ money($item->price) }}</td>
                        <td>
                            <span class="badge {{ $item->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                {{ $item->is_active ? 'مفعّل' : 'موقوف' }}
                            </span>
                        </td>
                        <td class="space-x-2 space-x-reverse">
                            <a href="{{ route('admin.items.edit', $item) }}" class="text-sm font-bold text-brand-700">تعديل</a>
                            <form class="inline" method="POST" action="{{ route('admin.items.destroy', $item) }}" onsubmit="return confirm('حذف المنتج؟')">
                                @csrf @method('DELETE')
                                <button class="text-sm font-bold text-red-600">حذف</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="py-10 text-center text-slate-400">لا توجد منتجات</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($items->hasPages())
        <div class="border-t border-slate-100 px-4 py-3">{{ $items->links() }}</div>
    @endif
</div>
@endsection
