<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'نقطة البيع') — {{ tranzit('name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="min-h-screen bg-slate-200 text-slate-800">
    <header class="sticky top-0 z-40 border-b border-brand-800 bg-pos text-white" x-data="{ navOpen: false }">
        <div class="flex items-center justify-between gap-2 px-3 py-3 sm:px-5">
            <div class="flex items-center gap-2 sm:gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-white/10 text-base sm:h-10 sm:w-10 sm:text-lg">☕</div>
                <div>
                    <div class="text-sm font-extrabold leading-tight sm:text-base">{{ tranzit('name_ar') }}</div>
                    <div class="hidden text-[11px] text-slate-300 sm:block">{{ auth()->user()->name }} — {{ auth()->user()->roleLabel() }}</div>
                </div>
            </div>

            <nav class="hidden items-center gap-1 text-sm font-semibold md:flex">
                <a href="{{ route('pos.index') }}" class="rounded-lg px-3 py-2 {{ request()->routeIs('pos.index') ? 'bg-white/15' : 'hover:bg-white/10' }}">نقطة البيع</a>
                <a href="{{ route('pos.tables') }}" class="rounded-lg px-3 py-2 {{ request()->routeIs('pos.tables') ? 'bg-white/15' : 'hover:bg-white/10' }}">الطاولات</a>
                <a href="{{ route('pos.waiting.index') }}" class="rounded-lg px-3 py-2 {{ request()->routeIs('pos.waiting.*') ? 'bg-white/15' : 'hover:bg-white/10' }}">الانتظار</a>
                <a href="{{ route('pos.shift.index') }}" class="rounded-lg px-3 py-2 {{ request()->routeIs('pos.shift.*') ? 'bg-white/15' : 'hover:bg-white/10' }}">الشيفت</a>
                @if(auth()->user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="rounded-lg px-3 py-2 hover:bg-white/10">الإدارة</a>
                @endif
            </nav>

            <div class="flex items-center gap-1">
                <button type="button" class="rounded-lg px-3 py-2 text-sm hover:bg-white/10 md:hidden" @click="navOpen=!navOpen" aria-label="القائمة">☰</button>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="rounded-lg px-3 py-2 text-sm hover:bg-white/10" type="submit">خروج</button>
                </form>
            </div>
        </div>

        <div class="border-t border-white/10 px-3 py-2 md:hidden" x-show="navOpen" x-cloak>
            <div class="flex flex-col gap-1 text-sm font-semibold">
                <a href="{{ route('pos.index') }}" class="rounded-lg px-3 py-2 hover:bg-white/10">نقطة البيع</a>
                <a href="{{ route('pos.tables') }}" class="rounded-lg px-3 py-2 hover:bg-white/10">الطاولات</a>
                <a href="{{ route('pos.waiting.index') }}" class="rounded-lg px-3 py-2 hover:bg-white/10">الانتظار</a>
                <a href="{{ route('pos.shift.index') }}" class="rounded-lg px-3 py-2 hover:bg-white/10">الشيفت / الخزنة</a>
                @if(auth()->user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="rounded-lg px-3 py-2 hover:bg-white/10">الإدارة</a>
                @endif
            </div>
        </div>
    </header>

    @if (session('success'))
        <div class="mx-auto max-w-7xl px-3 pt-3">
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">{{ session('success') }}</div>
        </div>
    @endif
    @if (session('warning'))
        <div class="mx-auto max-w-7xl px-3 pt-3">
            <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-800">{{ session('warning') }}</div>
        </div>
    @endif
    @if ($errors->any())
        <div class="mx-auto max-w-7xl px-3 pt-3">
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
            </div>
        </div>
    @endif

    @yield('content')

    <style>
        [x-cloak] { display: none !important; }
        .pos-shell { min-height: 50vh; }
    </style>
    @stack('scripts')
</body>
</html>
