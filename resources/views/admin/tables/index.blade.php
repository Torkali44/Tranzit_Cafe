@extends('layouts.admin')
@section('title', 'الطاولات')
@section('subtitle', 'إدارة ترابيزات الكافيه')

@section('content')
<div class="mb-4 flex justify-end">
    <a href="{{ route('admin.tables.create') }}" class="btn-primary">+ ترابيزة جديدة</a>
</div>

<div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
    @foreach ($tables as $table)
        <div class="card p-4">
            <div class="mb-3 flex items-start justify-between gap-2">
                <div>
                    <div class="text-lg font-extrabold text-brand-900">{{ $table->name }}</div>
                    <div class="text-xs text-slate-500">{{ $table->area }} · {{ $table->capacity }} مقاعد</div>
                </div>
                <span class="badge
                    {{ $table->status === 'available' ? 'bg-emerald-100 text-emerald-700' : '' }}
                    {{ $table->status === 'occupied' ? 'bg-red-100 text-red-700' : '' }}
                    {{ $table->status === 'awaiting_payment' ? 'bg-amber-100 text-amber-800' : '' }}
                ">{{ $table->statusLabel() }}</span>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.tables.edit', $table) }}" class="btn-outline text-xs">تعديل</a>
                <form method="POST" action="{{ route('admin.tables.destroy', $table) }}" onsubmit="return confirm('حذف الترابيزة؟')">
                    @csrf @method('DELETE')
                    <button class="btn-ghost text-xs text-red-600">حذف</button>
                </form>
            </div>
        </div>
    @endforeach
</div>
@endsection
