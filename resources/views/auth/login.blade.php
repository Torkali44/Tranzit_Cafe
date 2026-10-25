@extends('layouts.guest')

@section('title', 'تسجيل الدخول')

@section('content')
<div class="w-full max-w-md rounded-3xl bg-white p-6 shadow-2xl sm:p-8">
    <div class="mb-8 text-center">
        <div class="mx-auto mb-3 flex h-16 w-16 items-center justify-center rounded-3xl bg-brand-900 text-3xl text-white">☕</div>
        <h1 class="text-2xl font-extrabold text-brand-900">{{ tranzit('name_ar') }}</h1>
        <p class="mt-1 text-sm text-slate-500">دخول آمن — أدمن أو كاشير</p>
    </div>

    <form method="POST" action="{{ route('login') }}" class="space-y-4" autocomplete="off">
        @csrf
        <div>
            <label class="label" for="username">اسم المستخدم</label>
            <input id="username" name="username" type="text" value="{{ old('username') }}" required autofocus
                   autocomplete="username" class="input @error('username') border-red-400 @enderror"
                   placeholder="مثال: admin">
            @error('username')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="label" for="password">كلمة المرور</label>
            <input id="password" name="password" type="password" required autocomplete="current-password" class="input"
                   placeholder="••••••••••">
        </div>
        <label class="flex items-center gap-2 text-sm text-slate-600">
            <input type="checkbox" name="remember" class="rounded border-slate-300">
            تذكرني على هذا الجهاز
        </label>
        <button type="submit" class="btn-primary w-full py-3 text-base">دخول</button>
    </form>

    <div class="mt-6 rounded-2xl bg-slate-50 p-3 text-xs leading-relaxed text-slate-500">
        <div class="mb-1 font-bold text-slate-700">حسابات تجريبية (غيّرها في الإنتاج):</div>
        <div>أدمن: <code class="font-bold">admin</code> / <code>Admin@Tranzit1</code></div>
        <div>كاشير: <code class="font-bold">cashier</code> / <code>Cashier@Tranzit1</code></div>
    </div>
</div>
@endsection
