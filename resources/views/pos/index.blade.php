@extends('layouts.pos')
@section('title', 'نقطة البيع')

@section('content')
@php
    $itemsPayload = $allItems->map(fn ($i) => [
        'id' => $i->id,
        'name' => $i->name,
        'price' => (float) $i->price,
        'category_id' => $i->category_id,
        'addons' => $i->addons->map(fn ($a) => [
            'id' => $a->id,
            'name' => $a->name,
            'price' => (float) $a->price,
        ])->values(),
    ])->values();

    $categoriesPayload = $categories->map(fn ($c) => [
        'id' => $c->id,
        'name' => $c->name,
        'items_count' => $c->items_count,
    ])->values();

    $orderPayload = $order ? [
        'id' => $order->id,
        'order_number' => $order->order_number,
        'type' => $order->type,
        'type_label' => $order->typeLabel(),
        'status' => $order->status,
        'status_label' => $order->statusLabel(),
        'subtotal' => (float) $order->subtotal,
        'service_percent' => (float) $order->service_percent,
        'service_amount' => (float) $order->service_amount,
        'discount_type' => $order->discount_type,
        'discount_value' => (float) $order->discount_value,
        'discount_amount' => (float) $order->discount_amount,
        'total' => (float) $order->total,
        'cashier' => $order->user?->name ?? auth()->user()->name,
        'table' => $order->cafeTable ? ['id' => $order->cafeTable->id, 'name' => $order->cafeTable->name] : null,
        'items' => $order->items->map(fn ($i) => [
            'id' => $i->id,
            'item_name' => $i->item_name,
            'unit_price' => (float) $i->unit_price,
            'quantity' => $i->quantity,
            'addons' => $i->addons ?? [],
            'addons_label' => $i->addonsLabel(),
            'addons_total' => (float) $i->addons_total,
            'line_total' => (float) $i->line_total,
        ])->values(),
    ] : null;

    $tablesPayload = $availableTables->map(fn ($t) => [
        'id' => $t->id,
        'name' => $t->name,
        'number' => $t->number,
        'area' => $t->area,
    ])->values();

    $posConfig = [
        'categories' => $categoriesPayload,
        'allItems' => $itemsPayload,
        'initialOrder' => $orderPayload,
        'availableTables' => $tablesPayload,
        'tablesUrl' => $tablesUrl,
        'pendingTableId' => $pendingTable?->id,
        'pendingTableName' => $pendingTable?->name,
        'servicePercent' => (float) $servicePercent,
        'currency' => tranzit('currency'),
        'csrf' => csrf_token(),
        'routes' => [
            'createOrder' => route('pos.orders.create'),
            'addItem' => url('/pos/orders/__ORDER__/items'),
            'updateItem' => url('/pos/orders/__ORDER__/items/__ITEM__'),
            'removeItem' => url('/pos/orders/__ORDER__/items/__ITEM__'),
            'discount' => url('/pos/orders/__ORDER__/discount'),
            'assignTable' => url('/pos/orders/__ORDER__/table'),
            'save' => url('/pos/orders/__ORDER__/save'),
            'complete' => url('/pos/orders/__ORDER__/complete'),
            'pay' => url('/pos/orders/__ORDER__/pay'),
            'cancel' => url('/pos/orders/__ORDER__/cancel'),
            'print' => url('/invoices/__INVOICE__/print'),
        ],
    ];
@endphp

<script type="application/json" id="pos-config">{!! json_encode($posConfig, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>

<div
    class="pos-shell relative mx-auto flex max-w-[1600px] flex-col gap-3 p-2 pb-24 sm:p-3 lg:h-[calc(100vh-72px)] lg:flex-row lg:overflow-hidden lg:pb-3"
    x-data="posApp"
>
    <template x-if="error && (!allItems || !allItems.length)">
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" x-text="error"></div>
    </template>

    {{-- Mobile cart FAB --}}
    <button type="button"
            class="fixed bottom-4 start-4 z-40 flex items-center gap-2 rounded-2xl bg-emerald-600 px-4 py-3 text-sm font-bold text-white shadow-lg lg:hidden"
            @click="cartOpen = true">
        <span>الطلب</span>
        <span class="rounded-lg bg-white/20 px-2 py-0.5" x-text="itemsCount"></span>
        <span x-text="money(order?.total || 0)"></span>
    </button>

    <div class="fixed inset-0 z-50 bg-black/40 lg:hidden" x-show="cartOpen" x-cloak @click="cartOpen=false"></div>

    {{-- Cart panel --}}
    <aside
        class="fixed inset-x-0 bottom-0 z-50 flex max-h-[90vh] w-full translate-y-full flex-col overflow-hidden rounded-t-3xl bg-white shadow-2xl transition-transform duration-300 lg:static lg:order-1 lg:z-auto lg:max-h-none lg:w-[380px] lg:translate-y-0 lg:rounded-3xl lg:border lg:border-slate-200 lg:shadow-sm xl:w-[420px]"
        :class="cartOpen ? '!translate-y-0' : ''"
    >
        <div class="bg-gradient-to-l from-brand-900 to-brand-700 px-4 py-4 text-white">
            <div class="mb-2 flex justify-center lg:hidden">
                <div class="h-1.5 w-12 rounded-full bg-white/30"></div>
            </div>
            <div class="flex items-start justify-between gap-3">
                <div>
                    <div class="text-lg font-extrabold" x-text="order ? ('طلب #' + order.order_number) : 'طلب جديد'"></div>
                    <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-white/80">
                        <span class="rounded-full bg-white/15 px-2 py-0.5" x-text="order?.type_label || (pendingTableId ? 'داخلي' : 'سفري')"></span>
                        <span class="rounded-full bg-emerald-400/20 px-2 py-0.5 text-emerald-100" x-show="order?.table" x-text="order?.table?.name"></span>
                        <span class="rounded-full bg-emerald-400/20 px-2 py-0.5 text-emerald-100" x-show="!order?.table && pendingTableName" x-text="pendingTableName"></span>
                        <span x-show="!order?.table && !pendingTableId" class="rounded-full bg-amber-400/20 px-2 py-0.5 text-amber-100">بدون طاولة</span>
                    </div>
                </div>
                <div class="text-end">
                    <div class="text-[11px] text-white/70" x-text="order?.cashier || '{{ auth()->user()->name }}'"></div>
                    <button type="button" class="mt-1 text-sm text-white/90 lg:hidden" @click="cartOpen=false">إغلاق</button>
                </div>
            </div>
        </div>

        <div class="flex-1 min-h-0 space-y-2 overflow-y-auto bg-slate-50 p-3">
            <template x-if="!order || !order.items.length">
                <div class="flex h-full min-h-[200px] flex-col items-center justify-center rounded-2xl border border-dashed border-slate-200 bg-white text-sm text-slate-400">
                    <div class="mb-1 text-2xl opacity-50">☕</div>
                    اضغط على صنف لإضافته
                </div>
            </template>
            <template x-for="line in (order?.items || [])" :key="line.id">
                <div class="rounded-2xl border border-slate-100 bg-white p-3.5 shadow-sm">
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0">
                            <div class="font-bold text-brand-900" x-text="line.item_name"></div>
                            <div class="mt-0.5 text-xs text-slate-500" x-show="line.addons_label" x-text="line.addons_label"></div>
                        </div>
                        <div class="shrink-0 font-extrabold text-brand-800" x-text="money(line.line_total)"></div>
                    </div>
                    <div class="mt-3 flex items-center gap-2">
                        <div class="inline-flex items-center rounded-xl bg-slate-100 p-1">
                            <button type="button" class="flex h-8 w-8 items-center justify-center rounded-lg bg-white text-lg font-bold shadow-sm" @click="changeQty(line, line.quantity - 1)" :disabled="line.quantity <= 1">−</button>
                            <span class="w-8 text-center font-bold" x-text="line.quantity"></span>
                            <button type="button" class="flex h-8 w-8 items-center justify-center rounded-lg bg-white text-lg font-bold shadow-sm" @click="changeQty(line, line.quantity + 1)">+</button>
                        </div>
                        <button type="button" class="ms-auto rounded-lg px-2 py-1 text-xs font-bold text-red-600 hover:bg-red-50" @click="removeItem(line)">حذف</button>
                    </div>
                </div>
            </template>
        </div>

        <div class="shrink-0 border-t border-slate-100 bg-white p-3 sm:p-4">
            <div class="mb-3 space-y-1 rounded-2xl bg-slate-50 px-3 py-2.5 text-sm">
                <div class="flex justify-between"><span class="text-slate-500">الإجمالي</span><span class="font-semibold" x-text="money(order?.subtotal || 0)"></span></div>
                <div class="flex justify-between"><span class="text-slate-500" x-text="'خدمة ' + (order?.service_percent || servicePercent) + '%'"></span><span x-text="money(order?.service_amount || 0)"></span></div>
                <div class="flex justify-between border-t border-slate-200 pt-2 text-lg font-extrabold text-brand-900">
                    <span>المطلوب</span><span x-text="money(order?.total || 0)"></span>
                </div>
            </div>

            <div class="space-y-2">
                <div class="grid grid-cols-2 gap-2">
                    <button type="button" class="rounded-xl bg-emerald-600 py-3 text-sm font-extrabold text-white transition hover:bg-emerald-700 disabled:opacity-50" @click="openPay()" :disabled="loading || !order">
                        الدفع
                    </button>
                    <button type="button" class="rounded-xl bg-brand-800 py-3 text-sm font-extrabold text-white transition hover:bg-brand-900 disabled:opacity-50" @click="completeOrder()" :disabled="loading || !order">
                        إتمام الطلب
                    </button>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <button type="button" class="rounded-xl bg-slate-100 py-2.5 text-sm font-bold text-slate-700 transition hover:bg-slate-200 disabled:opacity-50" @click="saveOrder()" :disabled="loading || !order">
                        حفظ (تعليق)
                    </button>
                    <button type="button" class="rounded-xl bg-red-50 py-2.5 text-sm font-bold text-red-600 transition hover:bg-red-100 disabled:opacity-50" @click="askCancel()" :disabled="loading || !order">
                        إلغاء الطلب
                    </button>
                </div>
            </div>

            <p class="mt-2 text-center text-xs font-semibold text-emerald-700" x-show="success" x-text="success"></p>
            <p class="mt-2 text-center text-xs text-red-600" x-show="error" x-text="error"></p>
        </div>
    </aside>

    {{-- Products --}}
    <section class="order-1 flex min-w-0 flex-1 flex-col gap-3 lg:order-2 lg:overflow-hidden">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
            <input type="search" class="input" placeholder="ابحث عن منتج..." x-model="search">
            <a href="{{ route('pos.tables') }}" class="btn-outline shrink-0 whitespace-nowrap">شاشة الطاولات</a>
        </div>

        <div class="flex min-h-0 flex-1 flex-col gap-3 lg:flex-row lg:overflow-hidden">
            <div class="rounded-2xl bg-pos p-3 text-white lg:w-56 lg:shrink-0 xl:w-64">
                <input type="search" class="mb-3 w-full rounded-xl border-0 bg-white/10 px-3 py-2 text-sm text-white placeholder:text-slate-300 outline-none" placeholder="بحث في الأقسام..." x-model="categorySearch">
                <div class="flex gap-2 overflow-x-auto pb-1 lg:block lg:max-h-full lg:space-y-1 lg:overflow-y-auto lg:overflow-x-visible lg:pb-0">
                    <button type="button" class="pos-category whitespace-nowrap" :class="categoryId === 'all' && 'active'" @click="categoryId = 'all'">
                        <span>الكل</span>
                        <span class="text-xs opacity-80" x-text="allItems.length"></span>
                    </button>
                    <template x-for="cat in filteredCategories" :key="cat.id">
                        <button type="button" class="pos-category whitespace-nowrap" :class="String(categoryId) === String(cat.id) && 'active'" @click="categoryId = cat.id">
                            <span x-text="cat.name"></span>
                            <span class="text-xs opacity-80" x-text="cat.items_count"></span>
                        </button>
                    </template>
                </div>
            </div>

            <div class="min-h-0 flex-1 overflow-y-auto rounded-2xl bg-slate-100/80 p-2 sm:p-3">
                <div class="grid grid-cols-2 gap-2 sm:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5">
                    <template x-for="item in filteredItems" :key="item.id">
                        <button type="button" class="pos-item min-h-[110px]" @click="onItemClick(item)">
                            <span class="absolute start-2 top-2 rounded-lg bg-black/25 px-2 py-0.5 text-[11px] font-bold" x-text="money(item.price)"></span>
                            <span class="absolute end-2 top-2 text-white/70" x-show="item.addons.length">⚙</span>
                            <span class="px-2 text-sm font-extrabold leading-snug sm:text-base" x-text="item.name"></span>
                        </button>
                    </template>
                </div>
                <div class="py-16 text-center text-slate-400" x-show="!filteredItems.length">لا توجد منتجات</div>
            </div>
        </div>
    </section>

    {{-- Must pick table modal --}}
    <div class="fixed inset-0 z-[60] flex items-end justify-center bg-black/50 p-4 sm:items-center" x-show="tableModal" x-cloak>
        <div class="w-full max-w-md rounded-3xl bg-white p-5 shadow-2xl" @click.outside="tableModal=false">
            <h3 class="text-lg font-extrabold text-brand-900">لازم تحدد ترابيزة</h3>
            <p class="mt-2 text-sm leading-relaxed text-slate-600">
                عشان تقدر ترجع للطلب لما العميل يحاسب، احفظه على طاولة (مثلاً ترابيزة 4). من شاشة الطاولات تضغط عليها تاني وتكمّل الدفع.
            </p>
            <label class="label mt-4">اختَر الترابيزة</label>
            <select class="input" x-model="selectedTableId">
                <option value="">— اختَر —</option>
                <template x-for="t in availableTables" :key="t.id">
                    <option :value="t.id" x-text="t.name + (t.area ? (' · ' + t.area) : '')"></option>
                </template>
            </select>
            <p class="mt-2 text-xs text-red-600" x-show="!availableTables.length">مفيش ترابيزة فاضية دلوقتي.</p>
            <div class="mt-5 flex gap-2">
                <button type="button" class="btn-primary flex-1" @click="confirmTableAndSave()" :disabled="loading || !availableTables.length">تأكيد والحفظ</button>
                <button type="button" class="btn-outline" @click="tableModal=false">إلغاء</button>
            </div>
        </div>
    </div>

    {{-- Cancel modal --}}
    <div class="fixed inset-0 z-[60] flex items-center justify-center bg-black/50 p-4" x-show="cancelModal" x-cloak>
        <div class="w-full max-w-sm rounded-3xl bg-white p-5 shadow-2xl" @click.outside="cancelModal=false">
            <h3 class="text-lg font-extrabold text-brand-900">إلغاء الطلب؟</h3>
            <p class="mt-2 text-sm text-slate-600">هيتشال الطلب كله. لو مربوط بطاولة هترجع متاحة.</p>
            <div class="mt-5 flex gap-2">
                <button type="button" class="btn-danger flex-1" @click="confirmCancel()">تأكيد الإلغاء</button>
                <button type="button" class="btn-outline" @click="cancelModal=false">رجوع</button>
            </div>
        </div>
    </div>

    {{-- Addons --}}
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" x-show="addonModal" x-cloak>
        <div class="w-full max-w-md rounded-2xl bg-white p-5 shadow-xl" @click.outside="addonModal=false">
            <div class="mb-4 flex items-start justify-between gap-3">
                <div>
                    <h3 class="text-lg font-extrabold text-brand-900" x-text="(selectedItem?.name || '') + ' — إضافات'"></h3>
                    <p class="text-sm text-slate-500" x-text="money(selectedItem?.price || 0)"></p>
                </div>
                <button type="button" class="btn-ghost" @click="addonModal=false">✕</button>
            </div>
            <div class="mb-4 space-y-2">
                <template x-for="addon in (selectedItem?.addons || [])" :key="addon.id">
                    <label class="flex cursor-pointer items-center justify-between rounded-xl border border-slate-200 px-3 py-3 hover:bg-slate-50">
                        <span class="flex items-center gap-2 font-semibold">
                            <input type="checkbox" class="rounded border-slate-300" x-model="selectedAddons[addon.id]">
                            <span x-text="addon.name"></span>
                        </span>
                        <span class="text-sm font-bold text-brand-700" x-text="'+ ' + money(addon.price)"></span>
                    </label>
                </template>
            </div>
            <button type="button" class="btn-primary w-full py-3" @click="confirmAddons()">+ إضافة للطلب</button>
        </div>
    </div>

    {{-- Pay --}}
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" x-show="payModal" x-cloak>
        <div class="w-full max-w-sm rounded-2xl bg-white p-5 shadow-xl" @click.outside="payModal=false">
            <h3 class="mb-1 text-lg font-extrabold text-brand-900">الدفع</h3>
            <p class="mb-1 text-sm text-slate-500" x-show="order?.table" x-text="'الطاولة: ' + order.table.name"></p>
            <p class="mb-4 text-sm text-slate-500">المطلوب: <strong x-text="money(order?.total || 0)"></strong></p>
            <label class="label">المبلغ المدفوع</label>
            <input type="number" min="0" step="0.01" class="input mb-2" x-model="amountPaid">
            <div class="mb-4 text-sm text-slate-600">
                الباقي: <strong x-text="money(Math.max(0, Number(amountPaid||0) - Number(order?.total||0)))"></strong>
            </div>
            <div class="flex gap-2">
                <button type="button" class="btn-success flex-1" @click="confirmPay()" :disabled="loading">تأكيد الدفع</button>
                <button type="button" class="btn-outline" @click="payModal=false">إلغاء</button>
            </div>
        </div>
    </div>

    {{-- Invoice preview --}}
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" x-show="invoiceModal" x-cloak>
        <div class="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-2xl bg-white p-6 shadow-xl" @click.outside="invoiceModal=false">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-lg font-extrabold">معاينة الفاتورة</h3>
                <button type="button" class="btn-ghost" @click="invoiceModal=false">✕</button>
            </div>
            <div class="text-center">
                <div class="text-2xl font-extrabold text-brand-900">{{ tranzit('name_ar') }}</div>
                <div class="text-sm text-slate-500">{{ tranzit('name') }}</div>
            </div>
            <div class="my-4 space-y-1 text-sm" x-show="invoicePreview">
                <div class="flex justify-between"><span class="text-slate-500">فاتورة رقم</span><strong x-text="invoicePreview?.invoice_number"></strong></div>
                <div class="flex justify-between"><span class="text-slate-500">المكان</span><strong x-text="invoicePreview?.table_label || 'سفري'"></strong></div>
                <div class="flex justify-between"><span class="text-slate-500">المطلوب</span><strong x-text="money(invoicePreview?.total)"></strong></div>
                <div class="flex justify-between"><span class="text-slate-500">نقدي</span><strong x-text="money(invoicePreview?.amount_paid)"></strong></div>
                <div class="flex justify-between"><span class="text-slate-500">الباقي</span><strong x-text="money(invoicePreview?.change_amount)"></strong></div>
            </div>
            <p class="mb-4 text-center text-xs text-slate-500">{{ tranzit('thank_you') }}</p>
            <div class="flex flex-wrap gap-2">
                <button type="button" class="btn-primary flex-1" @click="printInvoice()">طباعة</button>
                <button type="button" class="btn-outline flex-1" @click="newOrder()">طلب جديد</button>
            </div>
        </div>
    </div>
</div>
@endsection
