<!DOCTYPE html>
<html lang="ar" dir="rtl" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'لوحة التحكم') — {{ tranzit('name_ar') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="h-full overflow-hidden bg-slate-100">
    <div class="flex h-full min-h-0" x-data="{ sidebarOpen: false }">
        {{-- Overlay mobile --}}
        <div x-show="sidebarOpen" x-cloak @click="sidebarOpen=false"
             class="fixed inset-0 z-40 bg-black/40 lg:hidden"></div>

        {{-- Sidebar ثابت — الزر الأخضر دايماً ظاهر تحت --}}
        <aside
            class="fixed inset-y-0 right-0 z-50 flex h-full w-72 -translate-x-full flex-col overflow-hidden bg-brand-900 text-white transition lg:static lg:translate-x-0 lg:shrink-0"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">
            <div class="shrink-0 flex items-center gap-3 border-b border-white/10 px-5 py-5">
                <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-white/10 text-xl">☕</div>
                <div>
                    <div class="text-lg font-extrabold">{{ tranzit('name_ar') }}</div>
                    <div class="text-xs text-slate-300">{{ tranzit('name') }} Cafe</div>
                </div>
            </div>

            <nav class="min-h-0 flex-1 space-y-1 overflow-y-auto overscroll-contain p-3">
                @php
                    $links = [
                        ['admin.dashboard', 'لوحة التحكم', 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-4 0h4'],
                        ['admin.categories.index', 'الأقسام', 'M4 6h16M4 12h16M4 18h16'],
                        ['admin.items.index', 'المنتجات', 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10'],
                        ['admin.tables.index', 'الطاولات', 'M4 6a2 2 0 012-2h12a2 2 0 012 2v2H4V6zm0 4h16v8a2 2 0 01-2 2H6a2 2 0 01-2-2v-8z'],
                        ['admin.waiting.index', 'الانتظار', 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                        ['admin.orders.index', 'الطلبات', 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2'],
                        ['admin.invoices.index', 'الفواتير', 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                        ['admin.shifts.index', 'مراجعة الشيفتات', 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                        ['admin.users.index', 'المستخدمون', 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0'],
                        ['admin.reports.index', 'التقارير', 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
                        ['admin.settings.edit', 'الإعدادات', 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z'],
                    ];
                @endphp
                @foreach ($links as [$route, $label, $icon])
                    <a href="{{ route($route) }}"
                       @click="sidebarOpen=false"
                       class="sidebar-link {{ request()->routeIs($route) || request()->routeIs(str_replace('.index', '.*', $route)) || request()->routeIs(str_replace('.edit', '.*', $route)) ? 'active' : '' }}">
                        <svg class="h-5 w-5 shrink-0 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $icon }}"/>
                        </svg>
                        <span>{{ $label }}</span>
                    </a>
                @endforeach
            </nav>

            <div class="shrink-0 border-t border-white/10 bg-brand-900 p-3">
                <a href="{{ route('pos.index') }}" class="btn w-full bg-emerald-500 text-white hover:bg-emerald-600">
                    شاشة نقطة البيع
                </a>
            </div>
        </aside>

        {{-- الجزء الأبيض: الهيدر ثابت + المحتوى يسكرول --}}
        <div class="flex min-h-0 min-w-0 flex-1 flex-col overflow-hidden">
            <header class="shrink-0 z-30 flex items-center justify-between gap-3 border-b border-slate-200 bg-white px-4 py-3 sm:px-6">
                <div class="flex items-center gap-3">
                    <button type="button" @click="sidebarOpen=true" class="btn-ghost rounded-xl p-2 lg:hidden" aria-label="القائمة">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    <div>
                        <h1 class="text-lg font-extrabold text-brand-900 sm:text-xl">@yield('title', 'لوحة التحكم')</h1>
                        <p class="hidden text-xs text-slate-500 sm:block">@yield('subtitle', 'إدارة كافيه ترانزيت')</p>
                    </div>
                </div>
                <div class="flex items-center gap-2 sm:gap-3">
                    <a href="{{ route('pos.tables') }}" class="btn-outline hidden sm:inline-flex">الطاولات</a>
                    <div class="rounded-xl bg-slate-100 px-3 py-2 text-xs sm:text-sm">
                        <span class="font-bold">{{ auth()->user()->name }}</span>
                        <span class="text-slate-500"> — {{ auth()->user()->roleLabel() }}</span>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="btn-ghost text-red-600" type="submit">خروج</button>
                    </form>
                </div>
            </header>

            <main class="min-h-0 flex-1 overflow-y-auto overscroll-contain p-4 sm:p-6">
                @if (session('success'))
                    <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">
                        {{ session('success') }}
                    </div>
                @endif
                @if ($errors->any())
                    <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        <ul class="list-disc pe-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @yield('content')
            </main>
        </div>
    </div>

    <style>[x-cloak]{display:none!important}</style>
    @stack('scripts')
</body>
</html>
