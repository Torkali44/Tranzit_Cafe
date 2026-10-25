<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $cafeNameAr }} — {{ $cafeName }} Café</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --gold: #c5a059;
            --gold-soft: #d4b06e;
            --ink: #0c0a09;
            --panel: rgba(18, 14, 12, 0.82);
        }
        .landing-body {
            font-family: 'Cairo', sans-serif;
            background: #0a0807;
            color: #f5f0e8;
            min-height: 100dvh;
        }
        .landing-bg {
            background:
                radial-gradient(ellipse 80% 50% at 20% 20%, rgba(197,160,89,.18), transparent 55%),
                radial-gradient(ellipse 60% 40% at 90% 80%, rgba(120,70,30,.25), transparent 50%),
                linear-gradient(160deg, #120e0c 0%, #1a1410 45%, #0a0807 100%);
        }
        .landing-wood {
            background-image:
                linear-gradient(rgba(8,6,5,.55), rgba(8,6,5,.72)),
                url('https://images.unsplash.com/photo-1447933601403-0c6688de566e?auto=format&fit=crop&w=2000&q=80');
            background-size: cover;
            background-position: center;
        }
        .gold-text { color: var(--gold); }
        .btn-gold {
            display: inline-flex; align-items: center; justify-content: center; gap: .6rem;
            background: linear-gradient(135deg, #d4b06e, #c5a059 50%, #a8843f);
            color: #1a1208; font-weight: 800; border-radius: 999px;
            padding: .85rem 1.6rem; box-shadow: 0 10px 30px rgba(197,160,89,.35);
            transition: transform .2s ease, box-shadow .2s ease, filter .2s ease;
        }
        .btn-gold:hover { transform: translateY(-2px); filter: brightness(1.05); box-shadow: 0 14px 34px rgba(197,160,89,.45); }
        .nav-pill {
            display: inline-flex; align-items: center; gap: .4rem;
            padding: .55rem .95rem; border-radius: 999px; font-size: .82rem; font-weight: 700;
            color: rgba(245,240,232,.78); transition: .2s ease;
            white-space: nowrap;
        }
        .nav-pill:hover { background: rgba(255,255,255,.08); color: #fff; }
        .nav-pill.active { background: var(--gold); color: #1a1208; }
        .glass-panel {
            background: var(--panel);
            border: 1px solid rgba(197,160,89,.22);
            backdrop-filter: blur(16px);
            box-shadow: 0 20px 50px rgba(0,0,0,.45);
        }
        .tile {
            position: relative; overflow: hidden; border-radius: 1.1rem;
            min-height: 92px; isolation: isolate;
            border: 1px solid rgba(197,160,89,.2);
            transition: transform .25s ease, box-shadow .25s ease;
        }
        .tile:hover { transform: translateY(-3px) scale(1.02); box-shadow: 0 12px 28px rgba(0,0,0,.4); }
        .tile img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; z-index: -2; }
        .tile::after {
            content: ''; position: absolute; inset: 0; z-index: -1;
            background: linear-gradient(to top, rgba(8,6,5,.88) 10%, rgba(8,6,5,.25) 70%);
        }
        .fade-up { animation: fadeUp .7s ease both; }
        .fade-up-delay-1 { animation-delay: .1s; }
        .fade-up-delay-2 { animation-delay: .2s; }
        .fade-up-delay-3 { animation-delay: .35s; }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(18px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .float-soft { animation: floatSoft 5s ease-in-out infinite; }
        @keyframes floatSoft {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-8px); }
        }
        .hero-cup {
            animation: cupIn .9s cubic-bezier(.2,.8,.2,1) both;
        }
        @keyframes cupIn {
            from { opacity: 0; transform: scale(.92) translateY(20px); }
            to { opacity: 1; transform: scale(1) translateY(0); }
        }
        .side-banner {
            background-image:
                linear-gradient(to bottom, rgba(10,8,7,.15), rgba(10,8,7,.55)),
                url('https://images.unsplash.com/photo-1554118811-1e0d58224f24?auto=format&fit=crop&w=900&q=80');
            background-size: cover;
            background-position: center;
        }
        @media (max-width: 1023px) {
            .side-banner { min-height: 180px; border-radius: 1.25rem; }
        }
    </style>
</head>
<body class="landing-body landing-bg">
@php
    $isAdmin = auth()->check() && auth()->user()->isAdmin();
    $cta = auth()->check()
        ? ($isAdmin ? route('admin.dashboard') : route('pos.index'))
        : route('login');
    $ctaLabel = auth()->check() ? 'ادخل النظام' : 'ابدأ البيع الآن';
    $salesUrl = $isAdmin ? route('admin.reports.index') : (auth()->check() ? route('pos.index') : route('login'));
    $ordersUrl = $isAdmin ? route('admin.orders.index') : (auth()->check() ? route('pos.index') : route('login'));
    $settingsUrl = $isAdmin ? route('admin.settings.edit') : (auth()->check() ? route('pos.shift.index') : route('login'));
@endphp

<div class="relative min-h-dvh overflow-x-hidden">
    {{-- Top nav --}}
    <header class="relative z-20 mx-auto flex max-w-[1600px] items-center justify-between gap-3 px-3 py-3 sm:px-5 lg:px-8">
        <div class="flex items-center gap-3 fade-up">
            <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-[var(--gold)]/15 text-xl ring-1 ring-[var(--gold)]/40">☕</div>
            <div>
                <div class="text-base font-extrabold tracking-wide sm:text-lg">{{ $cafeName }} <span class="gold-text">Café</span></div>
                <div class="hidden text-[11px] text-white/50 sm:block">Good Coffee • Better Mood</div>
            </div>
        </div>

        <nav class="hidden items-center gap-1 rounded-full border border-white/10 bg-black/30 p-1 backdrop-blur md:flex">
            <a href="{{ route('home') }}" class="nav-pill active">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l9-9 9 9M5 10v10h14V10"/></svg>
                الصفحة الرئيسية
            </a>
            <a href="{{ $salesUrl }}" class="nav-pill">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13l-1.5 7h13M7 13l-2-8m5 15a1 1 0 100-2 1 1 0 000 2zm8 0a1 1 0 100-2 1 1 0 000 2z"/></svg>
                المبيعات
            </a>
            <a href="{{ $ordersUrl }}" class="nav-pill">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                الطلبات
            </a>
            <a href="{{ $settingsUrl }}" class="nav-pill">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4M17 17H7m10 0v-4m0 0V7a2 2 0 00-2-2H9a2 2 0 00-2 2v6m10 0H7"/></svg>
                الطابعات
            </a>
            <a href="{{ $settingsUrl }}" class="nav-pill">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                الإعدادات
            </a>
        </nav>

        <div class="flex items-center gap-2 fade-up">
            <div class="hidden text-end sm:block">
                <div class="text-sm font-extrabold">{{ $cafeNameAr }}</div>
                <div class="text-[11px] text-white/50">كافيه {{ $cafeNameAr }}</div>
            </div>
            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-[var(--gold)] text-lg text-[#1a1208] ring-2 ring-white/10">☕</div>
            <a href="{{ $cta }}" class="btn-gold !rounded-xl !px-3 !py-2 text-xs sm:hidden">{{ $ctaLabel }}</a>
        </div>
    </header>

    {{-- Mobile nav --}}
    <div class="mx-auto flex max-w-[1600px] gap-2 overflow-x-auto px-3 pb-2 md:hidden">
        <a href="{{ route('home') }}" class="nav-pill active shrink-0">الرئيسية</a>
        <a href="{{ $salesUrl }}" class="nav-pill shrink-0">المبيعات</a>
        <a href="{{ $ordersUrl }}" class="nav-pill shrink-0">الطلبات</a>
        <a href="{{ $cta }}" class="nav-pill shrink-0">{{ $ctaLabel }}</a>
    </div>

    <main class="relative z-10 mx-auto grid max-w-[1600px] gap-4 px-3 pb-8 pt-2 sm:px-5 lg:grid-cols-[1fr_280px] lg:gap-5 lg:px-8 xl:grid-cols-[minmax(0,1fr)_300px_260px]">
        {{-- Center stage --}}
        <section class="landing-wood relative overflow-hidden rounded-[1.75rem] border border-white/10 p-4 shadow-2xl sm:p-6 lg:p-8 xl:col-span-1">
            <div class="pointer-events-none absolute inset-0 bg-gradient-to-l from-black/55 via-black/35 to-transparent"></div>

            <div class="relative grid items-center gap-6 lg:grid-cols-[1.05fr_.95fr]">
                <div class="fade-up">
                    <p class="mb-2 text-xs font-bold tracking-[0.2em] text-[var(--gold-soft)] sm:text-sm">TRANZIT CAFÉ</p>
                    <h1 class="text-3xl font-extrabold leading-[1.25] text-white sm:text-4xl xl:text-5xl">
                        مرحباً بك في<br>
                        <span class="gold-text">{{ $cafeNameAr }} كافيه</span>
                    </h1>
                    <p class="mt-3 max-w-md text-sm leading-relaxed text-white/75 sm:text-base">
                        قهوة استثنائية .. بنكهات تلهمك
                    </p>

                    <div class="mt-5 flex flex-wrap gap-x-4 gap-y-2 text-xs font-semibold text-white/80 sm:text-sm">
                        <span class="inline-flex items-center gap-1.5"><span class="text-[var(--gold)]">☕</span> أجواء مريحة</span>
                        <span class="inline-flex items-center gap-1.5"><span class="text-[var(--gold)]">⚡</span> سرعة في الخدمة</span>
                        <span class="inline-flex items-center gap-1.5"><span class="text-[var(--gold)]">◆</span> جودة عالية</span>
                    </div>

                    <div class="mt-7">
                        <a href="{{ $cta }}" class="btn-gold fade-up-delay-1">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13l-1.5 7h13M7 13l-2-8m5 15a1 1 0 100-2 1 1 0 000 2zm8 0a1 1 0 100-2 1 1 0 000 2z"/></svg>
                            {{ $ctaLabel }}
                        </a>
                    </div>
                </div>

                <div class="relative flex justify-center hero-cup">
                    <div class="float-soft relative w-full max-w-[340px]">
                        <div class="absolute -inset-4 rounded-full bg-[var(--gold)]/15 blur-2xl"></div>
                        <img
                            src="https://images.unsplash.com/photo-1511920170033-f8396924c348?auto=format&fit=crop&w=900&q=80"
                            alt="{{ $cafeNameAr }} coffee"
                            class="relative z-10 mx-auto aspect-square w-full rounded-[2rem] object-cover shadow-2xl ring-1 ring-[var(--gold)]/30"
                            loading="eager"
                        >
                    </div>
                </div>
            </div>

            {{-- Categories --}}
            <div class="relative mt-8 fade-up fade-up-delay-2">
                <div class="mb-3 flex items-center justify-between gap-3">
                    <h2 class="text-base font-extrabold text-white sm:text-lg">اختر فئتك المفضلة</h2>
                    <a href="{{ $cta }}" class="text-xs font-bold text-[var(--gold)] hover:underline">عرض الكل</a>
                </div>
                <div class="grid grid-cols-2 gap-2.5 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6">
                    @foreach ($tiles as $tile)
                        <a href="{{ $cta }}" class="tile flex items-end p-2.5">
                            <img src="{{ $tile['image'] }}" alt="{{ $tile['name'] }}" loading="lazy">
                            <div class="w-full">
                                <div class="text-[11px] font-extrabold leading-snug text-[var(--gold-soft)] sm:text-xs">{{ $tile['name'] }}</div>
                                @if(!is_null($tile['price']))
                                    <div class="text-[10px] text-white/70">{{ money($tile['price']) }}</div>
                                @endif
                            </div>
                        </a>
                    @endforeach
                    <a href="{{ $cta }}" class="tile flex flex-col items-center justify-center gap-1 bg-[#161210] !min-h-[92px]">
                        <span class="flex h-9 w-9 items-center justify-center rounded-full border border-[var(--gold)]/50 text-[var(--gold)]">+</span>
                        <span class="text-[11px] font-bold text-[var(--gold-soft)]">المزيد من الأصناف</span>
                    </a>
                </div>
            </div>
        </section>

        {{-- Quick stats --}}
        <aside class="glass-panel fade-up fade-up-delay-2 flex flex-col rounded-[1.5rem] p-4 sm:p-5 lg:col-start-2 xl:col-start-2">
            <div class="mb-4 flex items-center gap-2">
                <span class="text-[var(--gold)]">✦</span>
                <h3 class="text-sm font-extrabold">خصائص سريعة</h3>
            </div>

            <div class="space-y-3">
                <div class="rounded-2xl border border-white/10 bg-black/25 p-3.5">
                    <div class="flex items-center justify-between gap-2">
                        <div class="text-xs text-white/60">إجمالي الطلبات اليوم</div>
                        <span class="text-[var(--gold)]">☰</span>
                    </div>
                    <div class="mt-1 text-2xl font-extrabold">{{ $ordersToday }}</div>
                </div>
                <div class="rounded-2xl border border-white/10 bg-black/25 p-3.5">
                    <div class="flex items-center justify-between gap-2">
                        <div class="text-xs text-white/60">إجمالي المبيعات اليوم</div>
                        <span class="text-[var(--gold)]">💰</span>
                    </div>
                    <div class="mt-1 text-xl font-extrabold gold-text">{{ money($salesToday) }}</div>
                </div>
                <div class="rounded-2xl border border-white/10 bg-black/25 p-3.5">
                    <div class="flex items-center justify-between gap-2">
                        <div class="text-xs text-white/60">المتوسط لكل طلب</div>
                        <span class="text-[var(--gold)]">↗</span>
                    </div>
                    <div class="mt-1 text-xl font-extrabold">{{ money($avgToday) }}</div>
                </div>
            </div>

            <div class="mt-auto pt-5 text-center">
                <div class="mx-auto mb-2 text-2xl text-[var(--gold)]">🌿</div>
                <p class="text-xs leading-relaxed text-white/65">
                    استمتع بكل كوب .. لأنك تستحق الأفضل
                </p>
                <a href="{{ $cta }}" class="btn-gold mt-4 w-full !rounded-2xl text-sm">{{ $ctaLabel }}</a>
            </div>
        </aside>

        {{-- Cafe banner --}}
        <aside class="side-banner fade-up fade-up-delay-3 relative hidden min-h-[420px] overflow-hidden rounded-[1.5rem] border border-white/10 shadow-2xl xl:block xl:col-start-3">
            <div class="absolute inset-x-0 bottom-0 p-4">
                <div class="rounded-2xl bg-black/55 p-3 text-center backdrop-blur">
                    <div class="text-xs font-bold tracking-wide text-[var(--gold)]">GOOD COFFEE</div>
                    <div class="text-sm font-extrabold text-white">Better Mood</div>
                </div>
            </div>
        </aside>
    </main>

    <footer class="relative z-10 pb-6 text-center text-[11px] text-white/35">
        © {{ date('Y') }} {{ $cafeName }} Café · Good Coffee • Better Mood
    </footer>
</div>
</body>
</html>