@extends('layouts.admin')
@section('title', 'المستخدمون')
@section('subtitle', 'أدمن واحد للإدارة + كاشيرين للبيع')

@section('content')
<div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <p class="text-sm text-slate-500">دوران فقط: أدمن (لا يُحذف) وكاشير (يمكن إضافة أكثر من واحد).</p>
    <a href="{{ route('admin.users.create') }}" class="btn-primary">+ إضافة كاشير</a>
</div>

<div class="card overflow-hidden">
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>الاسم</th>
                    <th>اسم المستخدم</th>
                    <th>الدور</th>
                    <th>الحالة</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                    <tr>
                        <td class="font-bold">{{ $user->name }}</td>
                        <td><code class="rounded bg-slate-100 px-2 py-0.5 text-xs">{{ $user->username }}</code></td>
                        <td>
                            <span class="badge {{ $user->isAdmin() ? 'bg-brand-100 text-brand-800' : 'bg-slate-100 text-slate-700' }}">
                                {{ $user->roleLabel() }}
                            </span>
                        </td>
                        <td>
                            <span class="badge {{ $user->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                {{ $user->is_active ? 'مفعّل' : 'موقوف' }}
                            </span>
                        </td>
                        <td class="space-x-2 space-x-reverse">
                            <a href="{{ route('admin.users.edit', $user) }}" class="text-sm font-bold text-brand-700">تعديل</a>
                            @if($user->isCashier())
                                <form class="inline" method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('حذف هذا الكاشير؟')">
                                    @csrf @method('DELETE')
                                    <button class="text-sm font-bold text-red-600">حذف</button>
                                </form>
                            @else
                                <span class="text-xs text-slate-400">محمي</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
