@extends('layouts.admin')
@section('title', $user->exists ? 'تعديل مستخدم' : 'إضافة كاشير')

@section('content')
<div class="mx-auto max-w-xl card p-5 sm:p-6">
    @if($isAdminAccount ?? false)
        <div class="mb-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
            حساب <strong>الأدمن (الإدارة)</strong> — دور ثابت، لا يُحذف، ولا يمكن تحويله لكاشير.
        </div>
    @else
        <div class="mb-4 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
            الأدوار في النظام نوعان فقط: <strong>أدمن واحد</strong> للإدارة، و<strong>كاشير</strong> للبيع والشيفت. الإضافة هنا لكاشير جديد.
        </div>
    @endif

    <form method="POST" action="{{ $user->exists ? route('admin.users.update', $user) : route('admin.users.store') }}" class="space-y-4" autocomplete="off">
        @csrf
        @if($user->exists) @method('PUT') @endif

        <div>
            <label class="label">الاسم الظاهر</label>
            <input name="name" class="input" value="{{ old('name', $user->name) }}" required maxlength="120">
        </div>
        <div>
            <label class="label">اسم المستخدم (للدخول)</label>
            <input name="username" class="input" value="{{ old('username', $user->username) }}" required
                   pattern="[A-Za-z0-9._\-]+" minlength="4" maxlength="40" placeholder="مثال: cashier01">
            <p class="mt-1 text-xs text-slate-500">حروف إنجليزية وأرقام فقط · بدون مسافات</p>
            @error('username')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="label">البريد (اختياري)</label>
            <input type="email" name="email" class="input" value="{{ old('email', $user->email) }}" maxlength="120">
            @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="label">كلمة المرور {{ $user->exists ? '(اتركها فارغة إن لم تتغير)' : '' }}</label>
            <input type="password" name="password" class="input" {{ $user->exists ? '' : 'required' }} autocomplete="new-password" minlength="10">
            <p class="mt-1 text-xs text-slate-500">10 أحرف على الأقل + حرف كبير وصغير + رقم + رمز</p>
            @error('password')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="label">الدور</label>
            @if($isAdminAccount ?? false)
                <input class="input bg-slate-50" value="أدمن — الإدارة" disabled>
                <input type="hidden" name="role" value="admin">
            @else
                <input class="input bg-slate-50" value="كاشير — بيع وشيفت فقط" disabled>
                <input type="hidden" name="role" value="cashier">
            @endif
        </div>

        @unless($isAdminAccount ?? false)
            <label class="flex items-center gap-2 text-sm font-semibold">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $user->is_active ?? true) ? 'checked' : '' }}>
                مفعّل
            </label>
        @endunless

        <div class="flex flex-wrap gap-2 pt-2">
            <button class="btn-primary" type="submit">حفظ</button>
            <a href="{{ route('admin.users.index') }}" class="btn-outline">رجوع</a>
        </div>
    </form>
</div>
@endsection
